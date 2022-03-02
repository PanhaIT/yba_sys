<?php
include("includes/function.php");
// Setting
$allowBarcode = false;
$costDecimal  = 2;
$salesDecimal = 2;
$sqlSetting = mysql_query("SELECT * FROM s_module_detail_settings WHERE id IN (1, 39, 40) AND is_active = 1");
while($rowSetting = mysql_fetch_array($sqlSetting)){
    if($rowSetting['id'] == 1){
        if($rowSetting['is_checked'] == 1){
            $allowBarcode = true;
        } else if($rowSetting['id'] == 39){
            $costDecimal = $rowSetting['value'];
        } else if($rowSetting['is_checked'] == 40){
            $salesDecimal = $rowSetting['value'];
        }
    }
}
// Authentication
$this->element('check_access');
$allowEdit = checkAccess($user['User']['id'], $this->params['controller'], 'edit');
$allowDelete = checkAccess($user['User']['id'], $this->params['controller'], 'delete');
$allowSetPrice = checkAccess($user['User']['id'], $this->params['controller'], 'productPrice');
$allowViewCost = checkAccess($user['User']['id'], $this->params['controller'], 'viewCost');
$allowViewActivityByQtyGraph = checkAccess($user['User']['id'], $this->params['controller'], 'viewActivityByGraph');
$allowViewPurchaseSalesGraph = checkAccess($user['User']['id'], $this->params['controller'], 'viewPurchaseSalesByGraph');
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $(".btnBackProduct").click(function(event){
            event.preventDefault();
            oCache.iCacheLower = -1;
            oTableProductDashBoard.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        <?php
        if($allowDelete && $this->data['Product']['is_active']!=3){
        ?>
        $(".btnDeleteProduct").click(function(event){
            event.preventDefault();
            var name = "<?php echo $this->data['Product']['code']." - ".$this->data['Product']['name']; ?>";
            $("#dialog").dialog('option', 'title', '<?php echo DIALOG_CONFIRMATION; ?>');
            $("#dialog").html('<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_CONFIRM_DELETE; ?> <b>' + name + '</b>?</p>');
            $("#dialog").dialog({
                title: '<?php echo DIALOG_CONFIRMATION; ?>',
                resizable: false,
                modal: true,
                width: 'auto',
                height: 'auto',
                position: 'center',
                open: function(event, ui){
                    $(".ui-dialog-buttonpane").show();
                },
                buttons: {
                    '<?php echo ACTION_DELETE; ?>': function() {
                        $.ajax({
                            type: "GET",
                            url: "<?php echo $this->base.'/'.$this->params['controller']; ?>/delete/<?php echo $this->data['Product']['id']; ?>",
                            data: "",
                            beforeSend: function(){
                                $("#dialog").dialog("close");
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
                            },
                            success: function(result){
                                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                                $(".btnBackProduct").click();
                                if(result != '<?php echo MESSAGE_DATA_HAVE_CHILD; ?>' && result != '<?php echo MESSAGE_DATA_HAS_BEEN_DELETED; ?>'){
                                    createSysAct('Products', 'Delete', 2, result);
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                }else {
                                    createSysAct('Products', 'Delete', 1, '');
                                    // alert message
                                    $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                }
                                // alert message
                                $("#dialog").dialog({
                                    title: '<?php echo DIALOG_INFORMATION; ?>',
                                    resizable: false,
                                    modal: true,
                                    width: 'auto',
                                    height: 'auto',
                                    position: 'center',
                                    buttons: {
                                        '<?php echo ACTION_CLOSE; ?>': function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                            }
                        });
                    },
                    '<?php echo ACTION_CANCEL; ?>': function() {
                        $(this).dialog("close");
                    }
                }
            });
        });
        <?php
        }
        if($allowSetPrice && $this->data['Product']['is_active']!=3){
        ?>
        $(".btnSetPriceProduct").click(function(event){
            event.preventDefault();
            var obj = $(this);
            $.ajax({
                type:   "POST",
                url:    "<?php echo $this->base . "/products/productPrice/".$this->data['Product']['id']; ?>",
                beforeSend: function(){
                    obj.find("img").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                },
                success: function(msg){
                    obj.find("img").attr('src', '<?php echo $this->webroot; ?>img/button/salary.png');
                    $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    $("#dialog").html(msg);
                    $("#dialog").dialog({
                        title: '<?php echo ACTION_SET_PRICE; ?>',
                        resizable: false,
                        modal: true,
                        width: '95%',
                        height: '570',
                        position:'center',
                        open: function(event, ui){
                            $(".ui-dialog-buttonpane").show();
                        },
                        buttons: {
                            '<?php echo ACTION_SAVE; ?>': function() {
                                var formName = "#ProductPrice";
                                var validateBack =$(formName).validationEngine("validate");
                                if(!validateBack){
                                    return false;
                                }else{
                                    $(this).dialog("close");
                                    $.ajax({
                                        type: "POST",
                                        url: "<?php echo $this->base . '/' . $this->params['controller']; ?>/productPrice",
                                        data: $(":input").serialize(),
                                        beforeSend: function(){
                                            $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                                        },
                                        success: function(result){
                                            $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                                            if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>'){
                                                createSysAct('Products', 'Set Price', 2, result);
                                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
                                            }else {
                                                createSysAct('Products', 'Set Price', 1, '');
                                                // alert message
                                                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
                                            }
                                            $("#dialog").dialog({
                                                title: '<?php echo DIALOG_INFORMATION; ?>',
                                                resizable: false,
                                                modal: true,
                                                width: 'auto',
                                                height: 'auto',
                                                position: 'center',
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
                                }  
                            }
                        }
                    });
                }
            });
        });
        <?php
        }
        if($allowEdit && $this->data['Product']['is_active']!=3){
        ?>
        $(".btnEditProduct").click(function(event){
            event.preventDefault();
            // Back Product Dashboard
            var rightPanel = $(".btnBackProduct").parent().parent().parent();
            rightPanel.html("<?php echo ACTION_LOADING; ?>");
            rightPanel.load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/edit/<?php echo $this->data['Product']['id']; ?>");
        });
        <?php
        }
        if($allowViewActivityByQtyGraph){
        ?>
        // Total Sales By Graph
        $("#viewActivityByGraph").load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/viewActivityByGraph/<?php echo $this->data['Product']['id']; ?>/"+$("#filterActivityByGraph").val()+"/"+$("#groupActivityByGraph").val()+"/"+$("#chartActivityByGraph").val());
        <?php
        }
        if($allowViewPurchaseSalesGraph){
        ?>
        // Total Sales By Graph
        $("#viewPurchaseSalesGraph").load("<?php echo $this->base; ?>/<?php echo $this->params['controller']; ?>/viewPurchaseSalesByGraph/<?php echo $this->data['Product']['id']; ?>/"+$("#filterPurchaseSalesGraph").val()+"/"+$("#groupPurchaseSalesGraph").val()+"/"+$("#chartPurchaseSalesGraph").val());
        <?php
        }
        ?>
    });
    function popUpProductCatalog(mylink, windowname) { 
        if (! window.focus)
        return true; 
        var href; 
        if (typeof(mylink) == 'string') href=mylink; else href=mylink.href; 
        window.open(href, windowname, 'width=700,height=500,scrollbars=yes'); 
        return false; 
    }
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackProduct">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="float:right;">
        <?php
        if($allowDelete && $this->data['Product']['is_active']!=3){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnDeleteProduct">
                <img src="<?php echo $this->webroot; ?>img/button/delete.png" alt=""/>
                <?php echo ACTION_DELETE; ?>
            </a>
        </div>
        <?php
        }
        if($allowEdit && $this->data['Product']['is_active']!=3){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnEditProduct">
                <img src="<?php echo $this->webroot; ?>img/button/edit.png" alt=""/>
                <?php echo ACTION_EDIT; ?>
            </a>
        </div>
        <?php
        }
        if($allowSetPrice && $this->data['Product']['is_active']!=3){
        ?>
        <div class="buttons" style="float: right;">
            <a href="#" class="positive btnSetPriceProduct">
                <img src="<?php echo $this->webroot; ?>img/button/salary.png" alt=""/>
                <?php echo ACTION_SET_PRICE; ?>
            </a>
        </div>
        <?php
        }
        ?>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<table cellpadding="0" cellspacing="0" style="width: 100%;">
    <tr>
        <td style="vertical-align: top; text-align: center;">
            <?php
            if($this->data['Product']['photo'] != ''){
                $photo = 'public/product_photo/'.$this->data['Product']['photo'];
            } else {
                $photo = 'img/button/no-images.png';
            }
            ?>
            <img id="photoDisplay" alt="" src="<?php echo $this->webroot.$photo; ?>" style="max-width: 150px; max-height: 150px;" />
        </td>
        <td style="width: 50%; vertical-align: top;">
            <fieldset style="min-height: 230px;">
                <legend><?php __(MENU_PRODUCT_MANAGEMENT_INFO); ?></legend>
                <table cellpadding="5" cellspacing="0" style="width: 100%;">
                    <tr>
                        <td style="font-weight: bold; width: 20%;"><?php echo MENU_DEPARTMENT; ?> :</td>
                        <td>
                            <?php
                            $department = '';
                            $category   = '';
                            $subCategory = '';
                            $subSubCate  = '';
                            $sqlDep = mysql_query("SELECT departments.name AS dep_name, pgroups.id, pgroups.parent_id, pgroups.name FROM pgroups INNER JOIN departments ON departments.id = pgroups.department_id WHERE pgroups.id IN (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$this->data['Product']['id'].") LIMIT 1");
                            if(mysql_num_rows($sqlDep)){
                                $rowDep = mysql_fetch_array($sqlDep);
                                $department = $rowDep['dep_name'];
                                if($rowDep['parent_id'] != ''){
                                    $sqlM = mysql_query("SELECT name, parent_id FROM pgroups WHERE id = ".$rowDep['parent_id']);
                                    $rowM = mysql_fetch_array($sqlM);
                                    $subCategory = $rowM['name'];
                                    $subSubCate  = $rowDep['name'];
                                    if($rowM['parent_id'] != ''){
                                        $sqlC = mysql_query("SELECT name FROM pgroups WHERE id = ".$rowM['parent_id']);
                                        $rowC = mysql_fetch_array($sqlC);
                                        $category = $rowC['name'];
                                    }
                                } else {
                                    $category    = $rowDep['name'];
                                }
                            }
                            $sqlGroup = mysql_query("SELECT GROUP_CONCAT(name) FROM pgroups WHERE id IN (SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$this->data['Product']['id'].")");
                            $rowGroup = mysql_fetch_array($sqlGroup);
                            echo $department;
                            ?>
                        </td>
                        <td style="font-weight: bold; width: 20%;"><?php echo 'Category'; ?> :</td>
                        <td style="width: 25%;">
                            <?php
                            echo $category;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; width: 20%;">Sub Category</td>
                        <td>
                            <?php
                            echo $subCategory;
                            ?>
                        </td>
                        <td style="font-weight: bold; width: 20%;">Sub-Sub Category</td>
                        <td style="width: 25%;">
                            <?php
                            echo $subSubCate;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;"><?php echo TABLE_BRAND; ?> :</td>
                        <td>
                            <?php
                            if(!empty($this->data['Product']['brand_id'])){
                                $sqlB = mysql_query("SELECT name FROM brands WHERE id = ".$this->data['Product']['brand_id']);
                                $rowB = mysql_fetch_array($sqlB);
                                echo $rowB[0];
                            }
                            ?>
                        </td>
                        <td style="font-weight: bold;"><?php echo TABLE_COUNTRY; ?> :</td>
                        <td>
                            <?php
                            if(!empty($this->data['Product']['country_id'])){
                                $sqlC = mysql_query("SELECT name FROM countries WHERE id = ".$this->data['Product']['country_id']);
                                $rowC = mysql_fetch_array($sqlC);
                                echo $rowC[0];
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;"><?php echo TABLE_NAME; ?> :</td>
                        <td>
                            <?php
                            echo $this->data['Product']['name']." ";
                            if($this->data['Product']['file_catalog']){
                                echo '<a href="'.$this->webroot.'public/product_catalog/'.$this->data['Product']['file_catalog'].'" class="btnViewProductCatalog" onClick="return popUpProductCatalog(this, \'Product Catalog\')"><img alt="' . ACTION_VIEW_CATALOG . '" onmouseover="Tip(\'' . ACTION_VIEW_CATALOG . '\')" src="' . $this->webroot . 'img/button/catalog.png" style="width: 16px; height: 16px;" /></a>';
                            }
                            ?>
                        </td>
                        <td style="font-weight: bold;"><?php echo TABLE_BARCODE; ?> :</td>
                        <td>
                            <?php
                            echo $this->data['Product']['barcode'];
                            ?>
                        </td>
                        
                    </tr>
                    <tr>
                        <td style="font-weight: bold;"><?php echo TABLE_UOM; ?> :</td>
                        <td>
                            <?php
                            $sqlUom = mysql_query("SELECT name FROM uoms WHERE id = ".$this->data['Product']['price_uom_id']);
                            $rowUom = mysql_fetch_array($sqlUom);
                            echo $rowUom[0];
                            ?>
                        </td>
                        <td style="font-weight: bold;"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;"><?php echo TABLE_TRACK_EXPIRY_DATE; ?> :</td>
                        <td>
                            <?php
                            if($this->data['Product']['is_expired_date'] == 1){
                                echo ACTION_YES;
                            } else {
                                echo ACTION_NO;
                            }
                            ?>
                        </td>
                        <td style="font-weight: bold;"><?php echo TABLE_TRACK_LOT_SERIES; ?> :</td>
                        <td>
                            <?php
                            if($this->data['Product']['is_lots'] == 0){
                                echo ACTION_NO;
                            } else {
                                echo ACTION_YES;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; vertical-align: top;"><?php echo TABLE_SPEC; ?> :</td>
                        <td colspan="3">
                            <?php
                            echo nl2br($this->data['Product']['spec']);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; vertical-align: top;"><?php echo GENERAL_DESCRIPTION; ?> :</td>
                        <td colspan="3">
                            <?php
                            echo nl2br($this->data['Product']['description']);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;"><?php echo GENERAL_REORDER_LEVEL; ?> :</td>
                        <td>
                            <?php echo number_format($this->data['Product']['reorder_level'], 0)." ".$rowUom[0]; ?>
                        </td>
                        <td style="font-weight: bold;"><?php echo TABLE_CREATED; ?> :</td>
                        <td>
                            <?php echo dateShort($this->data['Product']['created'], "d/m/Y H:i:s"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;"><?php echo TABLE_CREATED_BY; ?> :</td>
                        <td>
                            <?php
                            $sqlUser = mysql_query("SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id = ".$this->data['Product']['created_by']);
                            $rowUser = mysql_fetch_array($sqlUser);
                            echo $rowUser[0];
                            ?>
                        </td>
                        <td style="font-weight: bold;"><?php echo TABLE_MODIFIED_BY; ?> :</td>
                        <td>
                            <?php
                            if($this->data['Product']['modified_by'] != ''){
                                $sqlUserM = mysql_query("SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id = ".$this->data['Product']['modified_by']);
                                $rowUserM = mysql_fetch_array($sqlUserM);
                                echo $rowUserM[0];
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;"><?php echo TABLE_VENDOR; ?> :</td>
                        <td colspan="3">
                            <?php
                            $sqlVendor = mysql_query("SELECT GROUP_CONCAT(name) FROM vendors WHERE id IN (SELECT vendor_id FROM product_vendors WHERE product_id = " . $this->data['Product']['id'].")");
                            $vendor    = mysql_fetch_array($sqlVendor);
                            echo $vendor[0];
                            ?>
                        </td>
                    </tr>
                </table>
            </fieldset> 
        </td>
        <td style=" vertical-align: top; width: 35%;">
            <fieldset style="min-height: 230px;">
                <legend>Unit Cost & Unit Price</legend>
                <table cellpadding="5" cellspacing="0" style="width: 100%;" class="table">
                    <tr>
                        <th class="first"><?php echo TABLE_TYPE; ?></th>
                        <?php
                        if($allowViewCost){
                        ?>
                        <th style="width: 15%;"><?php echo TABLE_UNIT_COST; ?>($)</th>
                        <?php
                            $width = '15%';
                        } else {
                            $width = '20%';
                        }
                        ?>
                        <th style="width: <?php echo $width; ?>;"><?php echo TABLE_PRICE; ?>($)</th>
                        <th style="width: 10%;"><?php echo TABLE_CREATED; ?></th>
                        <th style="width: 10%;"><?php echo TABLE_CREATED_BY; ?></th>
                    </tr>
                <?php
                    $sqlPrice = mysql_query("SELECT price_types.name, product_prices.*, uoms.abbr AS u_name, product_prices.created, users.username FROM product_prices INNER JOIN price_types ON price_types.id = product_prices.price_type_id INNER JOIN uoms ON uoms.id = product_prices.uom_id INNER JOIN users ON users.id = product_prices.created_by WHERE product_prices.uom_id = ".$this->data['Product']['price_uom_id']." AND product_prices.product_id = ".$this->data['Product']['id']);
                    if(mysql_num_rows($sqlPrice)){
                        while($rowPrice = mysql_fetch_array($sqlPrice)){
                            $unitCost = $this->data['Product']['unit_cost'];
                            if($rowPrice['set_type'] == 1){
                                $price = $rowPrice['amount'];
                            }else if($rowPrice['set_type'] == 2){
                                $percent = ($unitCost * $rowPrice['percent']) / 100;
                                $price = $unitCost + $percent;
                            }else if($rowPrice['set_type'] == 3){
                                $price = $unitCost + $rowPrice['add_on'];
                            }
                ?>
                    <tr>
                        <td class="first"><?php echo $rowPrice['name']; ?></td>
                        <?php
                        if($allowViewCost){
                        ?>
                        <td><?php echo number_format($unitCost, $costDecimal); ?></td>
                        <?php
                        }
                        ?>
                        <td><?php echo number_format($price, $salesDecimal); ?></td>
                        <td><?php echo dateShort($rowPrice['created'], "d/m/Y H:i:s"); ?></td>
                        <td><?php echo $rowPrice['username']; ?></td>
                    </tr>
                <?php
                        }
                    } else {
                ?>
                    <tr>
                        <td colspan="5" class="first"><?php echo TABLE_NO_RECORD; ?></td>
                    </tr>
                <?php
                    }
                ?>
                </table>
            </fieldset>
        </td>
    </tr>
    <?php
    if($allowViewActivityByQtyGraph){
    ?>
    <tr>
        <td></td>
        <td colspan="2">
            <div class="boxDashboard" style="width: 100%; font-size: 14px; font-weight: bold; margin-bottom: 10px; margin-top: 10px;">
            <h1 class="title"><span class="dashboardName"><?php echo TABLE_PRODUCT_ACTIVITY; ?></span>
                <img onmouseover="Tip('Loading...')" src="<?php echo $this->webroot; ?>img/button/refresh-animation.gif" id="loadingActivityByGraph" style="width: 20px; float: right; display: none; margin-right: 10px;" /> 
                <img onmouseover="Tip('Refresh')" src="<?php echo $this->webroot; ?>img/button/refresh-inactive.png" id="refreshActivityByGraph" class="refreshDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" /> 
                <div style="width: 370px; float: right;">
                    <select id="filterActivityByGraph" style="width: 130px; border: none;">
                        <option value="ThisWeek">This Week</option>
                        <option value="ThisWeekToDate">This Week-to-date</option>
                        <option value="ThisMonth">This Month</option>
                        <option value="LastWeek">Last Week</option>
                        <option value="LastWeekToDate">Last Week-to-date</option>
                        <option value="LastMonth">Last Month</option>
                    </select>
                    <select id="groupActivityByGraph" style="width: 130px; border: none;">
                        <option value="1">Group By Day</option>
                        <option value="2">Group By Month</option>
                        <option value="3">Group By Quarter</option>
                        <option value="4">Group By Year</option>
                    </select>
                    <select id="chartActivityByGraph" style="width: 100px; border: none;">
                        <option value="line">Line Chart</option>
                        <option value="column">Bar Chart</option>
                        <option value="area">Area Chart</option>
                    </select>
                </div>
                <div style="clear: both;"></div>
            </h1>
            <div style="width: 100%; font-size: 12px; height: 400px;" id="viewActivityByGraph">
                Loading....
            </div>
        </div>
        </td>
    </tr>
    <?php
    }
    if($allowViewPurchaseSalesGraph){
    ?>
    <tr>
        <td></td>
        <td colspan="2">
            <div class="boxDashboard" style="width: 100%; font-size: 14px; font-weight: bold; margin-bottom: 10px; margin-top: 10px;">
            <h1 class="title"><span class="dashboardName"><?php echo TABLE_TOTAL_PURCHASE_SALES_BY_AMOUNT; ?></span>
                <img onmouseover="Tip('Loading...')" src="<?php echo $this->webroot; ?>img/button/refresh-animation.gif" id="loadingPurchaseSalesGraph" style="width: 20px; float: right; display: none; margin-right: 10px;" /> 
                <img onmouseover="Tip('Refresh')" src="<?php echo $this->webroot; ?>img/button/refresh-inactive.png" id="refreshPurchaseSalesGraph" class="refreshDashboard" style="width: 20px; float: right; cursor: pointer; margin-right: 10px;" /> 
                <div style="width: 370px; float: right;">
                    <select id="filterPurchaseSalesGraph" style="width: 130px; border: none;">
                        <option value="ThisWeek">This Week</option>
                        <option value="ThisWeekToDate">This Week-to-date</option>
                        <option value="ThisMonth">This Month</option>
                        <option value="LastWeek">Last Week</option>
                        <option value="LastWeekToDate">Last Week-to-date</option>
                        <option value="LastMonth">Last Month</option>
                    </select>
                    <select id="groupPurchaseSalesGraph" style="width: 130px; border: none;">
                        <option value="1">Group By Day</option>
                        <option value="2">Group By Month</option>
                        <option value="3">Group By Quarter</option>
                        <option value="4">Group By Year</option>
                    </select>
                    <select id="chartPurchaseSalesGraph" style="width: 100px; border: none;">
                        <option value="line">Line Chart</option>
                        <option value="column">Bar Chart</option>
                        <option value="area">Area Chart</option>
                    </select>
                </div>
                <div style="clear: both;"></div>
            </h1>
            <div style="width: 100%; font-size: 12px; height: 400px;" id="viewPurchaseSalesGraph">
                Loading....
            </div>
        </div>
        </td>
    </tr>
    <?php
    }
    ?>
</table>
