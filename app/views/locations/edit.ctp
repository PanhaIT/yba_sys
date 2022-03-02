<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); 
?>
<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#LocationEditForm").validationEngine('attach', {
            isOverflown: true,
            overflownDIV: ".ui-tabs-panel"
        });
        $("#LocationEditForm").ajaxForm({
            beforeSerialize: function($form, options) {
                listbox_selectall('d', true);
            },
            beforeSubmit: function(arr, $form, options) {
                $(".txtSaveLocation").html("<?php echo ACTION_LOADING; ?>");
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
            },
            success: function(result) {
                $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
                $(".btnBackLocation").click();
                // alert message
                $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
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
        $(".btnBackLocation").click(function(event){
            event.preventDefault();
            $('#LocationEditForm').validationEngine('hideAll');
            oCache.iCacheLower = -1;
            oTableLocation.fnDraw(false);
            var rightPanel=$(this).parent().parent().parent();
            var leftPanel=rightPanel.parent().find(".leftPanel");
            rightPanel.hide();rightPanel.html("");
            leftPanel.show("slide", { direction: "left" }, 500);
        });
        
        $("#LocationAisle, #LocationBay, #LocationBin, #LocationLevel, #LocationDirection").blur(function(){
            var locationName = $("#LocationAisle").val()+"-"+$("#LocationBay").val()+"-L"+$("#LocationLevel").val()+"-"+$("#LocationBin").val()+"-"+$("#LocationDirection").val();
            $("#LocationName").val(locationName);
        });
    });
</script>
<div style="padding: 5px;border: 1px dashed #bbbbbb;">
    <div class="buttons">
        <a href="" class="positive btnBackLocation">
            <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
            <?php echo ACTION_BACK; ?>
        </a>
    </div>
    <div style="clear: both;"></div>
</div>
<br />
<?php echo $this->Form->create('Location'); 
echo $this->Form->input('id');
echo $this->Form->hidden('sys_code');?>
<fieldset>
    <legend><?php __(MENU_LOCATION_MANAGEMENT_INFO); ?></legend>
    <table>
        <tr>
            <td><label for="LocationLocationGroupId"><?php echo TABLE_LOCATION_GROUP; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php 
                    $totalQty = 0;
                    $sqlCheckTotal = mysql_query("SELECT SUM(IFNULL(total_qty, 0)) AS total_qty FROM {$this->data['Location']['id']}_inventory_totals GROUP BY product_id");
                    if(mysql_num_rows($sqlCheckTotal)){
                        $rowTotal = mysql_fetch_array($sqlCheckTotal);
                        $totalQty = $rowTotal['total_qty'];
                    }
                    if($totalQty == 0){
                        echo $this->Form->input('location_group_id', array('empty' => INPUT_SELECT, 'label' => false, 'class'=>'validate[required]')); 
                    } else {
                        echo $this->Form->hidden('location_group_id', array('value' => $this->data['Location']['location_group_id']));
                        $sqlWarehouse = mysql_query("SELECT name FROM location_groups WHERE id = ".$this->data['Location']['location_group_id']);
                        $rowWarehouse = mysql_fetch_array($sqlWarehouse);
                        echo $rowWarehouse[0];
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="LocationName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('name', array('class' => 'validate[required]')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="LocationAisle"><?php echo TABLE_AISLE; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('aisle', array()); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="LocationBay"><?php echo TABLE_BAY; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('bay', array()); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="LocationBin"><?php echo TABLE_BIN; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('bin', array()); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="LocationLevel"><?php echo TABLE_LEVEL; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('level', array()); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="LocationPosition"><?php echo TABLE_DIRECTION; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[Location][position]" id="LocationPosition" class="">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="R" <?php if($this->data['Location']['position'] == 'R'){ ?>selected=""<?php } ?>>R</option>
                        <option value="N" <?php if($this->data['Location']['position'] == 'N'){ ?>selected=""<?php } ?>>N</option>
                        <option value="L" <?php if($this->data['Location']['position'] == 'L'){ ?>selected=""<?php } ?>>L</option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="LocationColor"><?php echo TABLE_COLOR; ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <select name="data[Location][color]" id="LocationColor">
                        <option value=""><?php echo INPUT_SELECT; ?></option>
                        <option value="blue" <?php if($this->data['Location']['color'] == 'blue'){ ?>selected="selected"<?php } ?>>Blue</option>
                        <option value="green" <?php if($this->data['Location']['color'] == 'green'){ ?>selected="selected"<?php } ?>>Green</option>
                        <option value="red" <?php if($this->data['Location']['color'] == 'red'){ ?>selected="selected"<?php } ?>>Red</option>
                        <option value="orange" <?php if($this->data['Location']['color'] == 'orange'){ ?>selected="selected"<?php } ?>>Orange</option>
                        <option value="yellow" <?php if($this->data['Location']['color'] == 'yellow'){ ?>selected="selected"<?php } ?>>Yellow</option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="LocationIsForSale"><?php echo TABLE_FOR_SALE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->input('is_for_sale', array('empty' => INPUT_SELECT, 'label' => false, 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<fieldset style="display: none;">
    <legend><?php __(USER_USER_INFO); ?></legend>
    <table>
        <tr>
            <th>Available:</th>
            <th></th>
            <th>Member of:</th>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <select id="s" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $querySource=mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 AND id NOT IN (SELECT user_id FROM user_locations WHERE location_id=".$this->data['Location']['id'].")");
                    while($dataSource=mysql_fetch_array($querySource)){
                    ?>
                    <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="vertical-align: middle;">
                <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('s', 'd')" />
                <br /><br />
                <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('d', 's')" />
            </td>
            <td style="vertical-align: top;">
                <select id="d" name="data[Location][user_id][]" multiple="multiple" style="width: 300px; height: 200px;">
                    <?php
                    $queryDestination=mysql_query("SELECT DISTINCT user_id,(SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id = user_locations.user_id) AS full_name FROM user_locations WHERE location_id = ".$this->data['Location']['id']);
                    while($dataDestination=mysql_fetch_array($queryDestination)){
                    ?>
                    <option value="<?php echo $dataDestination['user_id']; ?>"><?php echo $dataDestination['full_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
    </table>
</fieldset>
<br />
<div class="buttons">
    <button type="submit" class="positive">
        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
        <span class="txtSaveLocation"><?php echo ACTION_SAVE; ?></span>
    </button>
</div>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>