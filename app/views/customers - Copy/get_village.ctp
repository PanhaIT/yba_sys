<?php
echo "<option value=''>".INPUT_SELECT."</option>";
foreach($villages as $village){
    echo "<option value='{$village['Village']['id']}' class='{$village['Village']['commune_id']}'>{$village['Village']['name']}</option>";
}
?>