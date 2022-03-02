<?php 
echo $this->Form->create('User', array('method'=>'POST','enctype'=>'multipart/form-data', 'class' => 'form form-vertical main-form','inputDefaults' => array('div' => false, 'label' => false)));
$description ='';
$querySource = mysql_query("SELECT description FROM user_share_todolists WHERE is_active=1  AND user_id ='".$user['User']['id']."' AND todo_list_id ='".$id."' LIMIT 01" );
if(mysql_num_rows($querySource)){
    $dataSource = mysql_fetch_array($querySource);
    $description = $dataSource[0];
}
?>
<div id="app form-body">
    <div class="row">
        <div class="col" style="margin-top:-15px;">
            <div class="col-12">
                <div class="form-group">
                    <label for="UserShareTodolistDescription"><?php echo TABLE_SHARE_TO_USER;?></label><label class="require-label">*</label>
                    <select name="data[User][user_id][]" multiple="multiple" required="required" class="userShareTodolist">
                        <?php
                        $selected ='';
                        $querySource = mysql_query("SELECT id,CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1  AND id !='".$user['User']['id']."' AND id !='".$owner."' AND id NOT IN (SELECT share_user_id FROM user_share_todolists WHERE todo_list_id ='" .$id. "')");
                            while($dataSource = mysql_fetch_array($querySource)){
                        ?>
                                <option value="<?php echo $dataSource['id']; ?>"><?php echo $dataSource['full_name']; ?></option>
                        <?php 
                            }
                        $queryDestination = mysql_query("SELECT DISTINCT share_user_id,(SELECT CONCAT(first_name,' ',last_name) full_name FROM users WHERE id = user_share_todolists.share_user_id) AS full_name FROM user_share_todolists WHERE todo_list_id = ".$id);
                            while($dataDestination = mysql_fetch_array($queryDestination)){
                        ?>
                                <option value="<?php echo $dataDestination['share_user_id']; ?>" selected="selected"><?php echo $dataDestination['full_name']; ?></option>
                        <?php 
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-12 card-custom">
                <div class="form-group">
                    <label for="UserShareTodolistDescription"><?php echo TABLE_REMARK;?></label>
                    <?php echo $this->Form->textarea('description', array('value' =>$description ,'class' => 'form-control','placeholder' => TABLE_REMARK ,'style' => 'height:120px;')); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->Form->end(); ?>

