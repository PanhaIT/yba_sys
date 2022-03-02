<script type="text/javascript">
    $(document).ready(function(){
        $("#view_type").change(function(){
            if($(this).val()==1){
                window.open("<?php echo $this->base; ?>/reports/userLogGraph","_self");
            }else if($(this).val()==2){
                window.open("<?php echo $this->base; ?>/reports/userLocation","_self");
            }else{
                window.open("<?php echo $this->base; ?>/reports/userActivity","_self");
            }
        });
    });
</script>
<form>
<div data-role="fieldcontain">
    <select name="view_type" id="view_type">
        <option value="1" <?php echo $this->params['action']=='userLogGraph'?'selected="selected"':''; ?>><?php echo MENU_USER_LOG; ?></option>
        <option value="2" <?php echo $this->params['action']=='userLocation'?'selected="selected"':''; ?>><?php echo MENU_USER_LOCATION; ?></option>
        <option value="3" <?php echo $this->params['action']=='userActivity'?'selected="selected"':''; ?>><?php echo MENU_USER_ACTIVITY; ?></option>
    </select>
</div>
</form>