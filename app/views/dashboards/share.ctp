<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        $("#ShareOption").change(function(){
            checkShareOption();
        });
        checkShareOption();
        calculateUser();
    });
    
    function checkShareOption(){
        var val = $("#ShareOption").val();
        if(val == 3 || val == 4){
            $("#divUserShare").show();
        } else {
            $("#divUserShare").hide();
            $("#userShareSelected").find("option").attr("selected", true);
            listbox_moveacross('userShareSelected', 'userShare');
        }
        $("#dialog").dialog({ position: 'center' });
    }
    
    function calculateUser(){
        var val = $("#ShareOption").val();
        var users = '';
        if(val == 3){
            users = $("#shareTos").val().toString().split(",");
        } else if(val == 4){
            users = $("#shareExcepts").val().toString().split(",");
        }
        if(users != ''){
            $.each(users, function( index, value ) {
                $("#userShare").find("option[value='"+value+"']").attr("selected", true);
            });
            listbox_moveacross('userShare', 'userShareSelected');
        }
    }

</script>
<?php echo $this->Form->create('Share', array('inputDefaults' => array('div' => false, 'label' => false))); ?>
<input type="hidden" id="shareTos" value="<?php echo $shareUser; ?>" />
<input type="hidden" id="shareExcepts" value="<?php echo $shareEcpt; ?>" />
<table>
    <tr>
        <td colspan="2"> 
            <input type="radio" name="saveOption" id="saveOpt1"  value="1" <?php if($saveOpt == 1){ ?>checked="checked" <?php } ?> /> <label for="saveOpt1">Share only this transaction.</label>
            <input type="radio" name="saveOption" id="saveOpt2"  value="2" <?php if($saveOpt == 2){ ?>checked="checked" <?php } ?> /> <label for="saveOpt2">Share all next transaction.</label>
        </td>
    </tr>
    <tr>
        <td style="width: 150px;"><label for="ShareOption"><?php echo TABLE_SHARE_OPTION; ?> <span class="red">*</span> :</label></td>
        <td> 
            <select id="ShareOption" style="width: 200px;">
                <option value="1" <?php if($shareOpt == 1){ ?>selected="selected"<?php } ?>><?php echo TABLE_SHARE_TO_ONLY_ME; ?></option>
                <option value="2" <?php if($shareOpt == 2){ ?>selected="selected"<?php } ?>><?php echo TABLE_SHARE_TO_EVERYONE; ?></option>
                <option value="3" <?php if($shareOpt == 3){ ?>selected="selected"<?php } ?>><?php echo TABLE_SHARE_TO_USER_CUSTOMIZE; ?></option>
                <option value="4" <?php if($shareOpt == 4){ ?>selected="selected"<?php } ?>><?php echo TABLE_SHARE_TO_USER_EXCEPT; ?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="2" id="divUserShare" style="display: none;">
            <fieldset>
                <legend><?php __(USER_USER_INFO); ?></legend>
                <table>
                    <tr>
                        <th>Available:</th>
                        <th></th>
                        <th>Members:</th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            <select id="userShare" multiple="multiple" style="width: 300px; height: 200px;">
                                <?php
                                $querySource=mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 AND id <> ".$user['User']['id']);
                                while($dataSource=mysql_fetch_array($querySource)){
                                ?>
                                <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td style="vertical-align: middle;">
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/right.png" style="cursor: pointer;" onclick="listbox_moveacross('userShare', 'userShareSelected')" />
                            <br /><br />
                            <img alt="" src="<?php echo $this->webroot; ?>img/button/left.png" style="cursor: pointer;" src="" style="cursor: pointer;" onclick="listbox_moveacross('userShareSelected', 'userShare')" />
                        </td>
                        <td style="vertical-align: top;">
                            <select id="userShareSelected" name="data[Pgroup][user_id][]" multiple="multiple" style="width: 300px; height: 200px;"></select>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<div style="clear: both;"></div>
<?php echo $this->Form->end(); ?>