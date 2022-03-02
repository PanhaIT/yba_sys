<?php 
    if(@$value){
        $split = explode("-", $value);
        $home  = str_replace("No", "", $split[0]);
        $home  = str_replace("ផ្ទះលេខ", "", $home);
        $street  = str_replace("St", "", $split[1]);
        $street  = str_replace("ផ្លូវលេខ", "", $street);
    }
?>
<script type="text/javascript">
    $(document).ready(function(){
        $(".province").click(function(){
            if($(this).val()!=""){
                $(".district").val('');
                $(".district option[class!='']").hide();
                $(".district option[class='"  + $(this).val() + "']").show();
            }else{
                $(".district").val('');
                $(".commune").val('');
                $(".village").val('');
                $(".district option").show();
                $(".commune option").show();
                var vi = 0
                $(".village").find("option").each(function(){
                    if(++vi != 1){
                        if($(this).val!=''){
                            $(this).remove();
                        }
                    }
                });
            }
            comboRefesh(".district",".province");
        });
        $(".district").change(function(){
            if($(this).val()!=""){
                $(".province").val($(".district").find("option:selected").attr("class"));
                $(".commune").val('');
                $(".commune option[class!='']").hide();
                $(".commune option[class='"  + $(this).val() + "']").show();
            }else{
                $(".commune").val('');
                $(".village").val('');
                $(".commune option").show();
                var vi = 0
                $(".village").find("option").each(function(){
                    if(++vi != 1){
                        if($(this).val!=''){
                            $(this).remove();
                        }
                    }
                });
            }
            comboRefesh(".commune",".district");
        });
        $(".commune").change(function(){
            if($(this).val()!=""){
                var commune = $(this).val();
                $(".district").val($(".commune").find("option:selected").attr("class"));
                $(".province").val($(".district").find("option:selected").attr("class"));
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->base."/customers/getVillage"; ?>",
                    data: "data[commune][id]=" + commune,
                    beforeSend: function(){
                        $(".loader").attr('src','<?php echo $this->webroot; ?>img/layout/spinner.gif');
                    },
                    success: function(msg){
                        $(".village").html(msg);
                        $(".loader").attr('src', '<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif');
                    }
                });
            }else{
                $(".village").val('');
                var vi = 0
                $(".village").find("option").each(function(){
                    if(++vi != 1){
                        if($(this).val!=''){
                            $(this).remove();
                        }
                    }
                });
            }
        });
    });
</script>
 <table cellpadding="5" id="form_address">
    <tr>
        <td><label for="home_no"><?php echo TABLE_HOME_NO; ?></label></td>
        <td><input type="text" id="home_no" value="<?php echo @$home; ?>"  style="height: 25px" /></td>
        <td><label for="street"><?php echo TABLE_STREET; ?></label></td>
        <td><input type="text" id="street" value="<?php echo @$street; ?>"  style="height: 25px" /></td>
    </tr>
    <tr>
        <td><label for="province_id"><?php echo TABLE_PROVINCE; ?>:</label</td>
        <td>
            <?php echo $this->Form->input('province_id', array('empty' => INPUT_SELECT, 'type'=>'select', 'selected' => @$provide_id ,'options' => $provinces, 'class'=>'province','label'=>false,'style'=>'width:145px;')); ?>
        </td>
        <td><label for="district_id"><?php echo TABLE_DISTRICT; ?>:</label></td>
        <td>
            <?php echo $this->Form->input('district_id', array('empty' => INPUT_SELECT, 'type'=>'select', 'selected' => @$district_id ,'options' => $districts , 'class'=>'district','label'=>false,'style'=>'width:145px;')); ?>
        </td>
    </tr>
    <tr>
        <td><label for="commune_id"><?php echo TABLE_COMMUNE; ?>:</label></td>
        <td>
            <?php echo $this->Form->input('commune_id', array('empty' => INPUT_SELECT, 'type'=>'select', 'selected' => @$commune_id ,'options' => $communes,'class'=>'commune','label'=>false,'style'=>'width:145px;')); ?>
        </td>
        <td><label for="village_id"><?php echo TABLE_VILLAGE; ?>:</label></td>
        <td>
            <?php
            if(@$villages){
            ?>
                <?php echo $this->Form->input('village_id', array('empty' => INPUT_SELECT, 'type'=>'select', 'options' => @$villages ,'selected' => @$village_id ,'class'=>'village','label'=>false,'style'=>'width:145px;')); ?>
            <?php
            }else{
            ?>
                <?php echo $this->Form->input('village_id', array('empty' => INPUT_SELECT, 'type'=>'select','class'=>'village','label'=>false,'style'=>'width:145px;')); ?>
            <?php
            }
            ?>
       </td>
    </tr>
</table>