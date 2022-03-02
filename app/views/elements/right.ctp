<script type="text/javascript">
    $(document).ready(function(){
        $("#project").change(function(){
            if($(this).val()!=''){
                window.open("<?php echo $this->base; ?>/reports/userLocation/" + $(this).val(),"_self");
            }else{
                window.open("<?php echo $this->base; ?>/reports/userLocation","_self");
            }
        });
    });
</script>
<form>
<div data-role="fieldcontain">
    <select id="project" name="select-native-1" id="project">
        <option value="">All</option>
        <?php
        $p_id = intval(UT_PROJECT_ID);
        $queryProject=mysql_query("SELECT id,name FROM level_0.projects WHERE is_active=1 AND (id = $p_id OR parent_id = $p_id) ORDER BY name");
        while($dataProject=mysql_fetch_array($queryProject)){
        ?>
        <option value="<?php echo $dataProject['id']; ?>" <?php echo isset($projectId) && $projectId==$dataProject['id']?'selected="selected"':''; ?>><?php echo $dataProject['name']; ?></option>
        <?php } ?>
    </select>
</div>
</form>