<script type="text/javascript">
    $(document).ready(function(){
        // Prevent Key Enter
        preventKeyEnter();
        <?php
        if(count($companies) != 1){
        ?>
        $("#PgroupCompanyId").chosen({width: '290px'}).change(function(){
            var companyId = $(this).chosen().val();
            $("#PgroupParentId").filterOptions('com', companyId, '');
            $("#PgroupParentId").chosen({width: '290px', max_shown_results: 4});
        });
        <?php
        } else {
        ?>
        var companyId = $("#PgroupCompanyId").val();
        $("#PgroupParentId").filterOptions('com', companyId, '');
        $("#PgroupParentId").chosen({width: '290px', max_shown_results: 4});
        <?php
        }
        ?>
    });
</script>
<br />
<?php 
echo $this->Form->create('Pgroup'); 
if(count($companies) == 1){
    $companyId = key($companies);
?>
<input type="hidden" value="<?php echo $companyId; ?>" name="data[Pgroup][company_id]" id="PgroupCompanyId" />
<?php
}
?>
<table style="width: 100%;" cellpadding="5">
    <?php
    if(count($companies) > 1){
    ?>
    <tr>
        <td style="width: 90px;"><label for="PgroupCompanyId"><?php echo TABLE_COMPANY; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php 
                $comArray = array('id' => 'PgroupCompanyId', 'label' => false, 'multiple' => 'multiple', 'data-placeholder' => INPUT_SELECT, 'style' => 'width: 424px;', 'name' => 'data[Pgroup][company_id]');
                if(count($companies) == 1){
                    $comArray = array('label' => false, 'style' => 'width: 424px;');
                }
                echo $this->Form->input('company_id', $comArray); 
                ?>
            </div>
        </td>
    </tr>
    <?php
    }
    ?>
    <tr>
        <td style="width: 90px;"><label for="PgroupParentId"><?php echo TABLE_SUB_OF_GROUP; ?>:</label></td>
        <td>
            <select id="PgroupParentId" name="data[Pgroup][parent_id]">
                <option value=""><?php echo INPUT_SELECT; ?></option>
                <?php
                $sqlParent = mysql_query("SELECT id, name, ics_apply_sub, (SELECT GROUP_CONCAT(company_id) FROM pgroup_companies WHERE pgroup_id = pgroups.id) AS company_id FROM pgroups WHERE is_active = 1 AND parent_id IS NULL AND id IN (SELECT pgroup_id FROM pgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))");
                while($rowParent = mysql_fetch_array($sqlParent)){
                ?>
                <option com="<?php echo $rowParent['company_id']; ?>" value="<?php echo $rowParent['id']; ?>"><?php echo $rowParent['name']; ?></option>
                <?php
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><label for="PgroupName"><?php echo TABLE_NAME; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width: 100%;">
                <?php echo $this->Form->text('name', array('id' => 'PgroupName', 'class' => 'validate[required]', 'name' => 'data[Pgroup][name]', 'style' => 'width: 280px; height: 30px; text-align: left;')); ?>
            </div>
        </td>
    </tr>
</table>
<?php echo $this->Form->end(); ?>