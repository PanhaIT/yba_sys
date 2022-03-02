<?php
if($type == 'adjustment'){
?>
<table cellpadding="5" cellspacing="0" style="margin-bottom: 10px;">
    <tr>
        <td><label for="branchIdImport"><?php echo TABLE_BRANCH; ?> :</label></td>
        <td>
            <div class="inputContainer" style="width:100%;">
                <select id="branchIdImport" style="width: 200px; height: 25px;">
                    <?php
                    if(count($branches) != 1){
                    ?>
                    <option value=""><?php echo INPUT_SELECT; ?></option>
                    <?php
                    }
                    foreach($branches AS $branch){
                    ?>
                    <option value="<?php echo $branch['Branch']['id']; ?>"><?php echo $branch['Branch']['name']; ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </td>
        <td><label for="locationGroupIdImport"><?php echo TABLE_LOCATION_GROUP; ?> :</label></td>
        <td>
            <div class="inputContainer" style="width:100%;">
                <select id="locationGroupIdImport" style="width: 200px; height: 25px;">
                    <?php
                    if(count($locationGroups) != 1){
                    ?>
                    <option value=""><?php echo INPUT_SELECT; ?></option>
                    <?php
                    }
                    foreach($locationGroups AS $locationGroup){
                    ?>
                    <option value="<?php echo $locationGroup['LocationGroup']['id']; ?>"><?php echo $locationGroup['LocationGroup']['name']; ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </td>
        <td><label for="locationIdImport"><?php echo TABLE_LOCATION; ?> :</label></td>
        <td>
            <div class="inputContainer" style="width:100%;">
                <select id="locationIdImport" style="width: 200px; height: 25px;">
                    <?php
                    if(count($locations) != 1){
                    ?>
                    <option value=""><?php echo INPUT_SELECT; ?></option>
                    <?php
                    }
                    foreach($locations AS $location){
                    ?>
                    <option value="<?php echo $location['Location']['id']; ?>"><?php echo $location['Location']['name']; ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </td>
        <td><label for="adjustmentDateImport"><?php echo TABLE_DATE; ?> :</label></td>
        <td>
            <div class="inputContainer" style="width:100%;">
                <input type="text" id="adjustmentDateImport" value="<?php echo date("d/m/Y"); ?>" style="width: 150px; height: 25px;" readonly="" />
            </div>
        </td>
    </tr>
    <tr>
        <td><label for="adjustmentAsImport"><?php echo TABLE_ADJUST_AS; ?> <span class="red">*</span> :</label></td>
        <td>
            <div class="inputContainer" style="width:100%">
                <?php
                $filter = " AND chart_account_type_id IN (10, 12, 13)";
                $adjAccountId = '56';
                ?>
                <select id="adjustmentAsImport" style="width: 200px; height: 25px;">
                    <?php
                    $query[0]=mysql_query("SELECT id, CONCAT(account_codes,' - ',account_description) AS name FROM chart_accounts WHERE ISNULL(parent_id) AND is_active=1 ".$filter." ORDER BY account_codes");
                    while($data[0]=mysql_fetch_array($query[0])){
                        $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[0]['id']);
                    ?>
                    <option value="<?php echo $data[0]['id']; ?>" <?php echo $data[0]['id'] == $adjAccountId ? 'selected="selected"' : ''; ?>><?php echo $data[0]['name']; ?></option>
                        <?php
                        $query[1]=mysql_query("SELECT id,CONCAT(account_codes,' - ',account_description) AS name FROM chart_accounts WHERE parent_id=".$data[0]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                        while($data[1]=mysql_fetch_array($query[1])){
                            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[1]['id']);
                        ?>
                        <option value="<?php echo $data[1]['id']; ?>" <?php echo $data[1]['id'] == $adjAccountId ? 'selected="selected"' : ''; ?> style="padding-left: 25px;"><?php echo $data[1]['name']; ?></option>
                            <?php
                            $query[2]=mysql_query("SELECT id,CONCAT(account_codes,' - ',account_description) AS name FROM chart_accounts WHERE parent_id=".$data[1]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                            while($data[2]=mysql_fetch_array($query[2])){
                                $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[2]['id']);
                            ?>
                            <option value="<?php echo $data[2]['id']; ?>" <?php echo $data[2]['id'] == $adjAccountId ? 'selected="selected"' : ''; ?> style="padding-left: 50px;"><?php echo $data[2]['name']; ?></option>
                                <?php
                                $query[3]=mysql_query("SELECT id,CONCAT(account_codes,' - ',account_description) AS name FROM chart_accounts WHERE parent_id=".$data[2]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                                while($data[3]=mysql_fetch_array($query[3])){
                                    $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[3]['id']);
                                ?>
                                <option value="<?php echo $data[3]['id']; ?>" <?php echo $data[3]['id'] == $adjAccountId ? 'selected="selected"' : ''; ?> style="padding-left: 75px;"><?php echo $data[3]['name']; ?></option>
                                    <?php
                                    $query[4]=mysql_query("SELECT id,CONCAT(account_codes,' - ',account_description) AS name FROM chart_accounts WHERE parent_id=".$data[3]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                                    while($data[4]=mysql_fetch_array($query[4])){
                                        $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[4]['id']);
                                    ?>
                                    <option value="<?php echo $data[4]['id']; ?>" <?php echo $data[4]['id'] == $adjAccountId ? 'selected="selected"' : ''; ?> style="padding-left: 100px;"><?php echo $data[4]['name']; ?></option>
                                        <?php
                                        $query[5]=mysql_query("SELECT id,CONCAT(account_codes,' - ',account_description) AS name FROM chart_accounts WHERE parent_id=".$data[4]['id']." AND is_active=1 ".$filter." ORDER BY account_codes");
                                        while($data[5]=mysql_fetch_array($query[5])){
                                            $queryIsNotLastChild=mysql_query("SELECT id FROM chart_accounts WHERE is_active=1 AND parent_id=".$data[5]['id']);
                                        ?>
                                        <option value="<?php echo $data[5]['id']; ?>" <?php echo mysql_num_rows($queryIsNotLastChild)?'disabled="disabled"':''; ?> <?php echo $data['id'] == $adjAccountId ? 'selected="selected"' : ''; ?> style="padding-left: 125px;"><?php echo $data[5]['name']; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>
        </td>
        <td colspan="4"></td>
    </tr>
</table>
<?php
}
?>
<input type="hidden" id="fileImportName" value="<?php echo $targetName; ?>" />
<table class="table" style="max-width: 500px; margin: 0px auto;">
    <tr>
        <th class="first"><?php echo TABLE_NO; ?></th>
        <?php
        foreach($csv[0] AS $i => $field){
            echo '<th>'.$field.'</th>';
        }
        ?>
    </tr>
    <?php
    $index = 0;
    foreach($csv AS $c => $col){
        if($c == 0){
            continue;
        }
        $checkValRequired = true;
        $tdColor = '';
        foreach($col AS $i => $val){ 
            if (in_array($csvRequired[$i], $match['required']) && $val == ''){
                $checkValRequired = false;
                $tdColor = ' style="color: #fff;"';
            } else {
                if($csvRequired[$i] == 'Product Barcode'){
                    $sqlP     = mysql_query("SELECT products.id, products.small_val_uom FROM products 
                            LEFT JOIN product_with_skus ON product_with_skus.id = products.id 
                            WHERE products.code = '".$val."' OR product_with_skus.sku = '".$val."' GROUP BY products.id LIMIT 1");
                    if(!mysql_num_rows($sqlP)){
                        $checkValRequired = false;
                        $tdColor = ' style="color: #fff;"';
                    }
                }
            }
        }
    ?>
    <tr<?php if($checkValRequired == false){ ?> style="background-color: red;"<?php } ?>>
        <td class="first"<?php echo $tdColor; ?>><?php echo  ++$index; ?></td>
        <?php
        foreach($col AS $i => $val){ 
            echo '<td'.$tdColor.'>'.$val.'</td>';
        }
        ?>
    </tr>
    <?php
    }
    ?>
</table>


