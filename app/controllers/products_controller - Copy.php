<?php

class ProductsController extends AppController {

    var $name = 'Products';
    var $components = array('Helper', 'ProductCom');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Dashboard');
        $branches = ClassRegistry::init('Branch')->find('all', array(
            'joins' => array(
                array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')
                )
            ),
            'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id']),
            'fields' => array('id', 'name'),
            'group' => array('Branch.id')));
        $this->set(compact('branches'));
    }

    function variantsProduct(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $vendors = explode(",",$_POST['vendor_id']);
        $colors  = explode(",",$_POST['color_id']);
        $sizes   = explode(",",$_POST['size_id']);
        $lastInsertId = 1;
        $sqlProduct = mysql_query("SELECT id FROM products ORDER BY id DESC LIMIT 1");
        if(mysql_num_rows($sqlProduct)){
            $rowProduct   = mysql_fetch_array($sqlProduct);
            $lastInsertId = $rowProduct[0]+1;
        }
        // mysql_query("TRUNCATE TABLE `product_variant_tmps`;");
        mysql_query( "DELETE FROM `product_variant_tmps` WHERE `user_id` = '".$user['User']['id']."' ");
        if (!empty($_POST['vendor_id']) && !empty($_POST['color_id']) && !empty($_POST['size_id'])) {
            for ($i = 0; $i < sizeof($vendors); $i++) {
                for ($j = 0; $j < sizeof($sizes); $j++) {
                    for ($k = 0; $k < sizeof($colors); $k++) {
                        mysql_query("INSERT INTO product_variant_tmps (photo,product_id, vendor_id, color_id, size_id,note,user_id) VALUES ('', '" . $lastInsertId . "','" .$vendors[$i] . "', '" . $colors[$k] . "', '" .$sizes[$j] . "','','".$user['User']['id']."')");
                    }
                }
            }
        }
        $productVariantTmp = ClassRegistry::init('ProductVariantTmp')->find('all', array('order' => 'id', 'conditions' => array('product_id' => $lastInsertId,'user_id' => $user['User']['id'])));
        $this->set(compact("productVariantTmp"));
    }

    function activeInactiveProduct($id = null,$isActive=null) {
        $result = array();
        if (!$id) {
            $result['error'] = 1;
            exit;
        }
        $user = $this->getCurrentUser();
        $modified = date("Y-m-d H:i:s");  
        $sqlCheckStock = mysql_query("SELECT SUM(total_qty) FROM inventory_totals WHERE product_id ='".$id."' ");
        $rowCheckStock = mysql_fetch_array($sqlCheckStock);
        if($rowCheckStock[0]>0){
            $result['error'] = 2;
        }else{
            if($isActive==1){
                $this->Product->updateAll(
                        array('Product.is_active' => "3", "Product.modified_by" => $user['User']['id'], 'Product.modified' => "'$modified'"),
                        array('Product.id' => $id)
                );
            }
            if($isActive==3){
                $this->Product->updateAll(
                        array('Product.is_active' => "1", "Product.modified_by" => $user['User']['id'], 'Product.modified' => "'$modified'"),
                        array('Product.id' => $id)
                );
            }
            $result['error'] = 0;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Close', $id);
        echo json_encode($result);
        exit;
    }

    function ajax($category, $displayPro, $department = 'all', $subCategory = 'all', $subsubCategory = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('category', 'displayPro', 'department', 'subCategory', 'subsubCategory'));
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->data = $this->Product->read(null, $id);
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'View', $id);
    }

    function upload($rel = null) {
        $this->layout = 'ajax';
        $note = '';
        if($rel != ""){
            $photoText = "photo_".$rel;
            $note = $_POST['data']['Product']['note'][$rel-1];
        }else{
            $photoText = "photoMain";
        }
        if ($_FILES[$photoText]['name'] != '') {
            $target_folder = 'public/product_photo/tmp/';
            $ext = explode(".", $_FILES[$photoText]['name']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            move_uploaded_file($_FILES[$photoText]['tmp_name'], $target_folder . $target_name);
            if (isset($_SESSION['pos_photo']) && $_SESSION['pos_photo'] != '') {
                @unlink($target_folder . $_SESSION['pos_photo']);
            }
            if($rel != ""){
                echo $_SESSION['pos_photo'] = $target_name."|*|".$rel."|*|".$note;
            }else{
                echo $_SESSION['pos_photo'] = $target_name;
            }
            exit();
        }
    }

    function removePhoto($id = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if ($id == null) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if(isset($_POST['photo'])){
            @unlink('public/product_photo/tmp/'.$_POST['photo']);
            @unlink('public/product_photo/tmp/thumbnail/'.$_POST['photo']);
            mysql_query("DELETE FROM `product_photos` WHERE id = '".$id."'");
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Delete Photo', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        }else{
            echo MESSAGE_DATA_INVALID;
            exit;
        }
    }

    function removePhotoTmp() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if ($_POST['photo'] == '') {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if(isset($_POST['photo'])){
            @unlink('public/product_photo/tmp/'.$_POST['photo']);
            @unlink('public/product_photo/tmp/thumbnail/'.$_POST['photo']);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        }else{
            echo MESSAGE_DATA_INVALID;
            exit;
        }
    }

    function cropPhoto() {
        $this->layout = 'ajax';
        // Function
        include('includes/function.php');
        $variantId   = $_POST['variant_id'];
        $variantNote = $_POST['note'];
        $_POST['photoFolder'] = str_replace("|||", "/", $_POST['photoFolder']);
        list($ImageWidth, $ImageHeight, $TypeCode) = getimagesize($_POST['photoFolder'] . $_POST['photoName']);
        $ImageType = ($TypeCode == 1 ? "gif" : ($TypeCode == 2 ? "jpeg" : ($TypeCode == 3 ? "png" : ($TypeCode == 6 ? "bmp" : FALSE))));
        $CreateFunction = "imagecreatefrom" . $ImageType;
        $OutputFunction = "image" . $ImageType;
        $target_name    = '';
        if ($ImageType) {
            $ImageSource = $CreateFunction($_POST['photoFolder'] . $_POST['photoName']);
            $ResizedImage = imagecreatetruecolor($_POST['w'], $_POST['h']);
            imagecopyresampled($ResizedImage, $ImageSource, 0, 0, $_POST['x'], $_POST['y'], $ImageWidth, $ImageHeight, $ImageWidth, $ImageHeight);
            imagejpeg($ResizedImage, $_POST['photoFolder'] . $_POST['photoName'], 100);
            // Rename
            $target_folder = 'public/product_photo/tmp/';
            $target_thumbnail = 'public/product_photo/tmp/thumbnail/';
            $ext = explode(".", $_POST['photoName']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            Resize($_POST['photoFolder'], $_POST['photoName'], $target_folder, $target_name, $_POST['w'], $_POST['h'], 100, true);
            Resize($_POST['photoFolder'], $_POST['photoName'], $target_thumbnail, $target_name, 300, 300, 100, true);
            @unlink($target_folder . $_POST['photoName']);
            if($variantId>0){
                mysql_query("UPDATE `product_variant_tmps` SET `photo` = '".$target_name."',`note` = '".$variantNote."' WHERE `product_variant_tmps`.`id` = '".$variantId. "';");
            }
        }
        echo $target_name; 
        exit();
    }

    function add($cloneId = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->Product->create();
            $dateNow  = date("Y-m-d H:i:s");
            $smValUom = ClassRegistry::init('UomConversion')->find('first', array('fileds' => array('value'), 'order' => 'id', 'conditions' => array('from_uom_id' => $this->data['Product']['price_uom_id'], 'is_small_uom = 1', 'is_active' => 1)));
            if (!empty($smValUom)) {
                $this->data['Product']['small_val_uom'] = $smValUom['UomConversion']['value'];
            } else {
                $this->data['Product']['small_val_uom'] = 1;
            }
//            if($this->data['Product']['barcode'] == ""){
//                $this->data['Product']['barcode'] = 'P';
//            }
            $unitCost = $this->data['Product']['unit_cost'] != "" ? str_replace(",", "", $this->data['Product']['unit_cost']) : 0;
            $this->data['Product']['sys_code']        = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['Product']['default_cost']    = $unitCost;
            $this->data['Product']['unit_cost']       = $unitCost;
            $this->data['Product']['reorder_level']   = $this->data['Product']['reorder_level']!=''?$this->data['Product']['reorder_level']:null;
            $this->data['Product']['created']         = $dateNow;
            $this->data['Product']['created_by']      = $user['User']['id'];
            $this->data['Product']['is_active']       = 1;
            if ($this->Product->save($this->data)) {
                $lastInsertId = $this->Product->id;
                // product main photo
                if ($this->data['Product']['photo'] != '') {
                    $ext = pathinfo($this->data['Product']['photo'], PATHINFO_EXTENSION);
                    $photoName =  $lastInsertId . '_' . md5($this->data['Product']['photo']).".".$ext;
                    rename('public/product_photo/tmp/' . $this->data['Product']['photo'], 'public/product_photo/' . $photoName);
                    rename('public/product_photo/tmp/thumbnail/' . $this->data['Product']['photo'], 'public/product_photo/tmp/thumbnail/' . $photoName);
                    mysql_query("UPDATE products SET photo='" . $photoName . "' WHERE id=" . $lastInsertId);
                    $this->data['Product']['photo'] = $photoName;
                }
                // Check Product Group Share
                $checkShare = 2;
                if (!empty($this->data['Product']['pgroup_id'])) {
                    $sqlShare = mysql_query("SELECT id FROM e_pgroup_shares WHERE pgroup_id = ".$this->data['Product']['pgroup_id']);
                    if(mysql_num_rows($sqlShare)){
                        $checkShare = 1;
                    }
                }
                if($checkShare == 1){
                    mysql_query("INSERT INTO `e_product_shares` (`company_id`, `product_id`, `created`, `created_by`) VALUES (".$this->data['Product']['company_id'].", ".$lastInsertId.", '".$dateNow."', ".$user['User']['id'].");");
                }
                // product multi photo
                if (!empty($this->data['photo'])){
                    for ($i = 0; $i < sizeof($this->data['photo']); $i++) {
                        if(!empty($this->data['photo'][$i]) && $this->data['photo'][$i] != ''){
                            $photoName =  $lastInsertId . '_' . md5($this->data['photo'][$i]);
                            rename('public/product_photo/tmp/' . $this->data['photo'][$i], 'public/product_photo/' . $photoName);
                            rename('public/product_photo/tmp/thumbnail/' . $this->data['photo'][$i], 'public/product_photo/tmp/thumbnail/' . $photoName);
                            // mysql_query("INSERT INTO `product_photos`(`product_id`, `photo`) VALUES ('".$lastInsertId."', '".$photoName."')");
                        }
                    }
                }
                // product group
                if (!empty($this->data['Product']['pgroup_id'])) {
                    mysql_query("INSERT INTO product_pgroups (product_id, pgroup_id) VALUES ('".$lastInsertId."', '".$this->data['Product']['pgroup_id']."')");
                }
                // SKU of each UOM
                if (!empty($this->data['sku_uom_value'])) {
                    for ($i = 0; $i < sizeof($this->data['sku_uom_value']); $i++) {
                        if ($this->data['sku_uom_value'][$i] != '' && $this->data['sku_uom'][$i] != '') {
                            mysql_query("INSERT INTO product_with_skus (product_id, sku, uom_id) VALUES ('" . $lastInsertId . "', '" . $this->data['sku_uom_value'][$i] . "', '" . $this->data['sku_uom'][$i] . "')");
                        }
                    }
                }
                // Product Vendor
                if (!empty($this->data['Product']['vendor_id'])) {
                    for ($i = 0; $i < sizeof($this->data['Product']['vendor_id']); $i++) {
                        mysql_query("INSERT INTO product_vendors (product_id, vendor_id) VALUES ('" . $lastInsertId . "', '" . $this->data['Product']['vendor_id'][$i] . "')");
                    }
                }
                // Product Size
                if (!empty($this->data['Product']['size_id'])) {
                    for ($i = 0; $i < sizeof($this->data['Product']['size_id']); $i++) {
                        mysql_query("INSERT INTO product_sizes (product_id, size_id) VALUES ('" . $lastInsertId . "', '" . $this->data['Product']['size_id'][$i] . "')");
                    }
                }
                // Product Color
                if (!empty($this->data['Product']['color_id'])) {
                    for ($i = 0; $i < sizeof($this->data['Product']['color_id']); $i++) {
                        mysql_query("INSERT INTO product_colors (product_id, color_id) VALUES ('" . $lastInsertId . "', '" . $this->data['Product']['color_id'][$i] . "')");
                    }
                }
                // Variant Product
                $vendorId = '';
                $colorId  = '';
                $sizeId   = '';
                $photo    = '';
                $note     = '';
                $sqlVariant = mysql_query("SELECT * FROM product_variant_tmps WHERE 1");
                if(mysql_num_rows($sqlVariant)){
                    while($rowVariant=mysql_fetch_array($sqlVariant)){
                        $vendorId = $rowVariant['vendor_id'];
                        $colorId  = $rowVariant['color_id'];
                        $sizeId   = $rowVariant['size_id'];
                        $photo    = $rowVariant['photo'];
                        $note     = $rowVariant['note'];
                        if(!empty($vendorId) && !empty($colorId) && !empty($sizeId)){
                            mysql_query("INSERT INTO product_variants(photo,product_id,vendor_id,color_id,size_id,note) 
                            VALUES ('".$photo ."','".$lastInsertId."','".$vendorId."','".$colorId."','".$sizeId."','".$note."')");
                        }
                    }
                    // mysql_query("TRUNCATE TABLE `product_variant_tmps`;");
                }
                // Product Branch
                mysql_query("INSERT INTO product_branches (product_id,branch_id) SELECT ".$lastInsertId.", id FROM branches WHERE is_active = 1;");
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Save Add New', $lastInsertId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if(!empty($cloneId)){
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Clone');
        }else{
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Add New');
        }

        // Vendor
        $vendors = ClassRegistry::init('Vendor')->find("list", array("conditions" => array("Vendor.is_active = 1"), "order" => "Vendor.name"));
        // Size
        $sizes   = ClassRegistry::init('Size')->find("list", array("conditions" => array("Size.is_active = 1"), "order" => "Size.name"));
        // Color
        $colors  = ClassRegistry::init('Color')->find("list", array("conditions" => array("Color.is_active = 1"), "order" => "Color.name"));
        $departments  = ClassRegistry::init('Department')->find("list", array("conditions" => array("Department.is_active = 1"), "order" => "Department.name"));
        $uoms      = ClassRegistry::init('Uom')->find("list", array("conditions" => array("Uom.is_active = 1"), "order" => "Uom.name"));
        $brands    = ClassRegistry::init('Brand')->find("list", array("conditions" => array("Brand.is_active = 1")));
        $countries = ClassRegistry::init('Country')->find("list", array("conditions" => array("Country.is_active = 1")));
        $vendors   = ClassRegistry::init('Vendor')->find("list", array("conditions" => array("Vendor.is_active = 1"), "order" => "Vendor.name"));
        $this->set(compact("uoms", "code", "cloneId", "brands", "countries", "vendors", "sizes", "colors", "departments"));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $makeProcess = false;
            // Check Product Tracsation
            $sqlCheckPgroupUse = mysql_query("SELECT id FROM inventories WHERE product_id = ".$id." LIMIT 1;");
            if(mysql_num_rows($sqlCheckPgroupUse)){
                $makeProcess = true;
            }
            $smValUom = ClassRegistry::init('UomConversion')->find('first', array('fileds' => array('value'), 'order' => 'id', 'conditions' => array('from_uom_id' => $this->data['Product']['price_uom_id'], 'is_small_uom = 1', 'is_active' => 1)));
            if (!empty($smValUom)) {
                $this->data['Product']['small_val_uom'] = $smValUom['UomConversion']['value'];
            } else {
                $this->data['Product']['small_val_uom'] = 1;
            }
//            $this->data['Product']['code']            = $this->data['Product']['barcode'];
            $this->data['Product']['reorder_level']   = $this->data['Product']['reorder_level']!=''?$this->data['Product']['reorder_level']:0;
            $this->data['Product']['modified_by']     = $user['User']['id'];
            if($makeProcess == false && !empty($this->data['Product']['default_cost'])){
                $this->data['Product']['unit_cost']   = $this->data['Product']['default_cost'];
            }
            if ($this->Product->save($this->data)) {
                // product Main photo
                if ($this->data['Product']['new_photo'] != '') {
                    $ext = pathinfo($this->data['Product']['new_photo'], PATHINFO_EXTENSION);
                    $photoName =  $this->data['Product']['id'] . '_' . $this->data['Product']['new_photo'].".".$ext;
                    rename('public/product_photo/tmp/' . $this->data['Product']['new_photo'], 'public/product_photo/' . $photoName);
                    rename('public/product_photo/tmp/thumbnail/' . $this->data['Product']['new_photo'], 'public/product_photo/tmp/thumbnail/' . $photoName);
                    @unlink('public/product_photo/' . $this->data['Product']['old_photo']);
                    @unlink('public/product_photo/tmp/thumbnail/' . $this->data['Product']['old_photo']);
                    mysql_query("UPDATE products SET photo='" . $photoName . "' WHERE id=" . $this->data['Product']['id']);
                    $this->data['Product']['photo'] = $photoName;
                }
                // product Multi photo
                if (!empty($this->data['new_photo'])) {    
                    mysql_query("DELETE FROM product_photos WHERE product_id = ".$this->data['Product']['id']);
                    // Insert Photo
                    for ($indexNewPhoto = 0; $indexNewPhoto < sizeof($this->data['new_photo']); $indexNewPhoto++) {
                        if(!empty($this->data['new_photo'][$indexNewPhoto]) && $this->data['new_photo'][$indexNewPhoto] != ''){
                            $extPhoto  = explode(".", $this->data['new_photo'][$indexNewPhoto]);
                            $sizePhoto = sizeof($extPhoto) - 1;
                            $photoName =  $this->data['Product']['id'] . '_' .md5($this->data['new_photo'][$indexNewPhoto]).".".$extPhoto[$sizePhoto];
                            rename('public/product_photo/tmp/' . $this->data['new_photo'][$indexNewPhoto], 'public/product_photo/' . $photoName);
                            rename('public/product_photo/tmp/thumbnail/' . $this->data['new_photo'][$indexNewPhoto], 'public/product_photo/tmp/thumbnail/' . $photoName);
                            mysql_query("INSERT INTO `product_photos`(`product_id`, `photo`) VALUES ('".$this->data['Product']['id']."', '".$photoName."')");
                        }
                    }
                }
                // product group
                mysql_query("DELETE FROM product_pgroups WHERE product_id=" . $id);
                if (!empty($this->data['Product']['pgroup_id'])) {
                    mysql_query("INSERT INTO product_pgroups (product_id,pgroup_id) VALUES ('" . $id . "','" . $this->data['Product']['pgroup_id'] . "')");
                }

                // SKU of each UOM
                mysql_query("DELETE FROM product_with_skus WHERE product_id=" . $id);
                if (!empty($this->data['sku_uom_value'])) {
                    for ($i = 0; $i < sizeof($this->data['sku_uom_value']); $i++) {
                        if ($this->data['sku_uom_value'][$i] != '' && $this->data['sku_uom'][$i] != '') {
                            mysql_query("INSERT INTO product_with_skus (product_id, sku, uom_id) VALUES ('" . $id . "', '" . $this->data['sku_uom_value'][$i] . "', '" . $this->data['sku_uom'][$i] . "')");
                        }
                    }
                }
                // Product Vendor
                mysql_query("DELETE FROM product_vendors WHERE product_id=" . $id);
                if (!empty($this->data['Product']['vendor_id'])) {
                    for ($i = 0; $i < sizeof($this->data['Product']['vendor_id']); $i++) {
                        mysql_query("INSERT INTO product_vendors (product_id, vendor_id) VALUES ('" . $id . "', '" . $this->data['Product']['vendor_id'][$i] . "')");
                    }
                }
                // Product Size
                mysql_query("DELETE FROM product_sizes WHERE product_id=" . $id);
                if (!empty($this->data['Product']['size_id'])) {
                    for ($i = 0; $i < sizeof($this->data['Product']['size_id']); $i++) {
                        mysql_query("INSERT INTO product_sizes (product_id, size_id) VALUES ('" . $lastInsertId . "', '" . $this->data['Product']['size_id'][$i] . "')");
                    }
                }
                // Product Color
                mysql_query("DELETE FROM product_colors WHERE product_id=" . $id);
                if (!empty($this->data['Product']['color_id'])) {
                    for ($i = 0; $i < sizeof($this->data['Product']['color_id']); $i++) {
                        mysql_query("INSERT INTO product_colors (product_id, color_id) VALUES ('" . $lastInsertId . "', '" . $this->data['Product']['color_id'][$i] . "')");
                    }
                }
                // Product Branch
                mysql_query("DELETE FROM product_branches WHERE product_id=" . $id);
                mysql_query("INSERT INTO product_branches (product_id,branch_id) SELECT ".$id.", id FROM branches WHERE is_active = 1;");
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Save Edit', $id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Save Edit (Error)', $id);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        if (empty($this->data)) {
            $this->data = $this->Product->read(null, $id);
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Edit', $id);
            $uoms       = ClassRegistry::init('Uom')->find("list", array("conditions" => array("Uom.is_active = 1")));
            $brands     = ClassRegistry::init('Brand')->find("list", array("conditions" => array("Brand.is_active = 1")));
            $countries  = ClassRegistry::init('Country')->find("list", array("conditions" => array("Country.is_active = 1")));
            // Vendor
            $vendors = ClassRegistry::init('Vendor')->find("list", array("conditions" => array("Vendor.is_active = 1"), "order" => "Vendor.name"));
            $vendorsSellecteds = ClassRegistry::init('ProductVendor')->find('list', array('fields' => array('id', 'vendor_id'), 'order' => 'id', 'conditions' => array('product_id' => $id)));
            $vendorsSellected  = array();
            foreach ($vendorsSellecteds as $ps) {
                array_push($vendorsSellected, $ps);
            }
            // Size
            $sizes   = ClassRegistry::init('Size')->find("list", array("conditions" => array("Size.is_active = 1"), "order" => "Size.name"));
            $sizeSellecteds = ClassRegistry::init('ProductSize')->find('list', array('fields' => array('id', 'vendor_id'), 'order' => 'id', 'conditions' => array('product_id' => $id)));
            $sizesSellected  = array();
            foreach ($sizesSellected as $ps) {
                array_push($sizesSellected, $ps);
            }
            // Color
            $colors  = ClassRegistry::init('Color')->find("list", array("conditions" => array("Color.is_active = 1"), "order" => "Color.name"));
            $colorsSellecteds = ClassRegistry::init('ProductColor')->find('list', array('fields' => array('id', 'vendor_id'), 'order' => 'id', 'conditions' => array('product_id' => $id)));
            $colorsSellected  = array();
            foreach ($colorsSellecteds as $ps) {
                array_push($colorsSellected, $ps);
            }
            
            $departments  = ClassRegistry::init('Department')->find("list", array("conditions" => array("Department.is_active = 1"), "order" => "Department.name"));
            $this->set(compact("uoms", "departments", "brands", "countries", "vendors", "sizes", "colors", "vendorsSellected", "sizesSellected", "colorsSellected"));
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow = date("Y-m-d H:i:s");
        $user    = $this->getCurrentUser();
        $this->data = $this->Product->read(null, $id);
        Configure::write('debug', 0);
        mysql_query("UPDATE `products` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        $error = mysql_error();
        if($error != 'Data cloud not been delete'){
            // Update Share
            $checkShare = mysql_query("SELECT id FROM e_product_shares WHERE product_id = ".$id);
            if(mysql_fetch_array($checkShare)){
                mysql_query("UPDATE `e_product_shares` SET is_active = 2 WHERE id = ".$id.";");
            }
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Delete', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Delete (Data cloud not been delete)', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }

    function product($company_id = null) {
        $this->layout = 'ajax';
        $this->set('company_id', $company_id);
    }

    function productAjax($company_id = null, $category = null) {
        $this->layout = 'ajax';
        $this->set('company_id', $company_id);
        $this->set('category', $category);
    }

    function searchProduct() {
        $this->layout = 'ajax';
        $products = $this->Product->find('all', array('conditions' => array('OR' => array('Product.name LIKE' => '%' . $this->params['url']['q'] . '%', 'Product.code LIKE' => '%' . $this->params['url']['q'] . '%', 'Product.price LIKE' => '%' . $this->params['url']['q'] . '%'), 'Product.is_active' => 1)));
        $this->set(compact('products'));
    }

    function searchProductByCode($company_id = null) {
        $this->layout = 'ajax';
        $product_code = !empty($this->data['code']) ? $this->data['code'] : "";
        $product_id = !empty($this->data['id']) ? $this->data['id'] : "";
        $product = $this->Product->find('first', array(
            'fields' => array(
                'Product.id',
                'Product.name',
                'Product.code',
                'Product.description',
                'Product.price',
                'Product.price_uom_id'
            ),
            'conditions' => array(
                array(
                    "OR" => array(
                        'Product.code' => $product_code,
                        'Product.id' => $product_id
                    )
                ),
                'Product.is_active' => 1,
                'Product.company_id' => $company_id
            ),
            'group' => array(
                'Product.id',
                'Product.name',
                'Product.code',
                'Product.description',
                'Product.price',
                'Product.price_uom_id',
            )
                ));
        $this->set(compact('product', 'pricingRules', 'timeSearch'));
    }

    function productPrice($id = null) {
        $this->layout = 'ajax';
        if (empty($id) && empty($this->data)) {
            echo '<b style="font-size: 18px;">'.MESSAGE_SELECT_BRANCH_TO_SHOW_PRICE_LIST.'</b>';
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if(!empty($this->data['type_id'])) {
                $k = 0;
                $dateNow  = date("Y-m-d H:i:s");
                $products = $this->Product->read(null, $this->data['ProductPrice']['product_id']);
                if($products['Product']['small_val_uom'] == 1){
                    // Delete Price
                    mysql_query("DELETE FROM product_qty_prices WHERE product_id = ".$products['Product']['id']);
                    // Add
                    if(!empty($this->data['price_6'])){
                        mysql_query("INSERT INTO `product_qty_prices` (`product_id`, `qty`, `unit_price`) VALUES (".$products['Product']['id'].", 6, ".$this->data['price_6'].");");
                    }
                    if(!empty($this->data['price_12'])){
                        mysql_query("INSERT INTO `product_qty_prices` (`product_id`, `qty`, `unit_price`) VALUES (".$products['Product']['id'].", 12, ".$this->data['price_12'].");");
                    }
                }
                // Save Edit Price
                $this->loadModel('EProductPrice');
                if($this->data['branch_id'] == 0){
                    $branches = ClassRegistry::init('Branch')->find('all', array('fields' => array('Branch.id', 'Branch.name'), 'conditions' => array('Branch.is_active = 1')));
                    // Price Type All Branch
                    for ($i = 0; $i <  sizeof($this->data['type_id']); $i++) {
                        $ProductPrice['ProductPrice']['branch_id']     = $this->data['branch_id'];
                        $ProductPrice['ProductPrice']['product_id']    = $this->data['ProductPrice']['product_id'];
                        $ProductPrice['ProductPrice']['price_type_id'] = $this->data['type_id'][$i];
                        for ($j = 0; $j < sizeof($this->data['uom_id']); $j++) {
                            $productPrice = ClassRegistry::init('ProductPrice')->find('first', array('conditions' => array('price_type_id' => $this->data['type_id'][$i], 'product_id' => $this->data['ProductPrice']['product_id'], 'uom_id' => $this->data['uom_id'][$j], 'branch_id' => $this->data['branch_id'])));
                            if (!empty($productPrice)) {
                                $ProductPrice['ProductPrice']['id'] = $productPrice['ProductPrice']['id'];
                                $ProductPrice['ProductPrice']['sys_code'] = $productPrice['ProductPrice']['sys_code'];
                            } else {
                                ClassRegistry::init('ProductPrice')->create();
                                $ProductPrice['ProductPrice']['id'] = null;
                                $ProductPrice['ProductPrice']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            }
                            $ProductPrice['ProductPrice']['uom_id'] = $this->data['uom_id'][$j];
                            $ProductPrice['ProductPrice']['old_unit_cost'] = $this->data['old_unit_cost'][$k];
                            $ProductPrice['ProductPrice']['amount_before'] = $this->data['amount_before'][$k];
                            $ProductPrice['ProductPrice']['amount']   = $this->data['amount'][$k];
                            $ProductPrice['ProductPrice']['percent']  = $this->data['percent'][$k];
                            $ProductPrice['ProductPrice']['add_on']   = $this->data['add_on'][$k];
                            $ProductPrice['ProductPrice']['set_type'] = $this->data['set_type'][$i];
                            $ProductPrice['ProductPrice']['created']  = $dateNow;
                            $ProductPrice['ProductPrice']['created_by'] = $user['User']['id'];
                            ClassRegistry::init('ProductPrice')->save($ProductPrice);
                            // Save Price to Product
                            if($ProductPrice['ProductPrice']['price_type_id'] == 2 && $ProductPrice['ProductPrice']['uom_id'] == $products['Product']['price_uom_id']){
                                $price = 0;
                                $unitCost = $products['Product']['unit_cost'];
                                if($ProductPrice['ProductPrice']['set_type'] == 1){
                                    $price = $ProductPrice['ProductPrice']['amount'];
                                }else if($ProductPrice['ProductPrice']['set_type'] == 2){
                                    $percent = ($unitCost * $ProductPrice['ProductPrice']['percent']) / 100;
                                    $price = $unitCost + $percent;
                                }else if($ProductPrice['ProductPrice']['set_type'] == 3){
                                    $price = $unitCost + $ProductPrice['ProductPrice']['add_on'];
                                }
                                mysql_query("UPDATE products SET unit_price = ".$price." WHERE id = ".$this->data['ProductPrice']['product_id']);
                            }
                            $k++;
                        }
                    }
                    // Update to Each Branch
                    foreach($branches AS $branch){
                        $k = 0;
                        $ProductPriceB = array();
                        for ($i = 0; $i <  sizeof($this->data['type_id']); $i++) {
                            $ProductPriceB['ProductPrice']['branch_id']     = $branch['Branch']['id'];
                            $ProductPriceB['ProductPrice']['product_id']    = $this->data['ProductPrice']['product_id'];
                            $ProductPriceB['ProductPrice']['price_type_id'] = $this->data['type_id'][$i];
                            for ($j = 0; $j < sizeof($this->data['uom_id']); $j++) {
                                $productPriceB = ClassRegistry::init('ProductPrice')->find('first', array('conditions' => array('price_type_id' => $this->data['type_id'][$i], 'product_id' => $this->data['ProductPrice']['product_id'], 'uom_id' => $this->data['uom_id'][$j], 'branch_id' => $branch['Branch']['id'])));
                                if (!empty($productPriceB)) {
                                    $ProductPriceB['ProductPrice']['id'] = $productPriceB['ProductPrice']['id'];
                                    $ProductPriceB['ProductPrice']['sys_code'] = $productPriceB['ProductPrice']['sys_code'];
                                } else {
                                    ClassRegistry::init('ProductPrice')->create();
                                    $ProductPriceB['ProductPrice']['id'] = null;
                                    $ProductPriceB['ProductPrice']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                                }
                                $ProductPriceB['ProductPrice']['uom_id'] = $this->data['uom_id'][$j];
                                $ProductPriceB['ProductPrice']['old_unit_cost'] = $this->data['old_unit_cost'][$k];
                                $ProductPriceB['ProductPrice']['amount_before'] = $this->data['amount_before'][$k];
                                $ProductPriceB['ProductPrice']['amount']   = $this->data['amount'][$k];
                                $ProductPriceB['ProductPrice']['percent']  = $this->data['percent'][$k];
                                $ProductPriceB['ProductPrice']['add_on']   = $this->data['add_on'][$k];
                                $ProductPriceB['ProductPrice']['set_type'] = $this->data['set_type'][$i];
                                $ProductPriceB['ProductPrice']['created']  = $dateNow;
                                $ProductPriceB['ProductPrice']['created_by'] = $user['User']['id'];
                                ClassRegistry::init('ProductPrice')->save($ProductPriceB);
                                $k++;
                            }
                        }
                    }
                } else {
                    for ($i = 0; $i <  sizeof($this->data['type_id']); $i++) {
                        $ProductPrice['ProductPrice']['branch_id']     = $this->data['branch_id'];
                        $ProductPrice['ProductPrice']['product_id']    = $this->data['ProductPrice']['product_id'];
                        $ProductPrice['ProductPrice']['price_type_id'] = $this->data['type_id'][$i];
                        for ($j = 0; $j < sizeof($this->data['uom_id']); $j++) {
                            $productPrice = ClassRegistry::init('ProductPrice')->find('first', array('conditions' => array('price_type_id' => $this->data['type_id'][$i], 'product_id' => $this->data['ProductPrice']['product_id'], 'uom_id' => $this->data['uom_id'][$j], 'branch_id' => $this->data['branch_id'])));
                            if (!empty($productPrice)) {
                                $ProductPrice['ProductPrice']['id'] = $productPrice['ProductPrice']['id'];
                                $ProductPrice['ProductPrice']['sys_code'] = $productPrice['ProductPrice']['sys_code'];
                            } else {
                                ClassRegistry::init('ProductPrice')->create();
                                $ProductPrice['ProductPrice']['id'] = null;
                                $ProductPrice['ProductPrice']['sys_code'] = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                            }
                            $ProductPrice['ProductPrice']['uom_id'] = $this->data['uom_id'][$j];
                            $ProductPrice['ProductPrice']['old_unit_cost'] = $this->data['old_unit_cost'][$k];
                            $ProductPrice['ProductPrice']['amount_before'] = $this->data['amount_before'][$k];
                            $ProductPrice['ProductPrice']['amount']   = $this->data['amount'][$k];
                            $ProductPrice['ProductPrice']['percent']  = $this->data['percent'][$k];
                            $ProductPrice['ProductPrice']['add_on']   = $this->data['add_on'][$k];
                            $ProductPrice['ProductPrice']['set_type'] = $this->data['set_type'][$i];
                            $ProductPrice['ProductPrice']['created']  = $dateNow;
                            $ProductPrice['ProductPrice']['created_by'] = $user['User']['id'];
                            ClassRegistry::init('ProductPrice')->save($ProductPrice);
                            // Send to E-Commerce
                            if($this->data['type_id'][$i] == 1){
                                $eprice = array();
                                $this->EProductPrice->create();
                                $eprice['EProductPrice']['product_id']   = $this->data['ProductPrice']['product_id'];
                                $eprice['EProductPrice']['uom_id']       = $this->data['uom_id'][$j];
                                $eprice['EProductPrice']['before_price'] = $this->data['amount_before'][$k];
                                $eprice['EProductPrice']['sell_price']   = $this->data['amount'][$k];
                                $eprice['EProductPrice']['created']      = $dateNow;
                                $eprice['EProductPrice']['created_by']   = $user['User']['id'];
                                $this->EProductPrice->save($eprice);
                            }
                            // Save Price to Product
                            if($ProductPrice['ProductPrice']['price_type_id'] == 2 && $ProductPrice['ProductPrice']['uom_id'] == $products['Product']['price_uom_id']){
                                $price = 0;
                                $unitCost = $products['Product']['unit_cost'];
                                if($ProductPrice['ProductPrice']['set_type'] == 1){
                                    $price = $ProductPrice['ProductPrice']['amount'];
                                }else if($ProductPrice['ProductPrice']['set_type'] == 2){
                                    $percent = ($unitCost * $ProductPrice['ProductPrice']['percent']) / 100;
                                    $price = $unitCost + $percent;
                                }else if($ProductPrice['ProductPrice']['set_type'] == 3){
                                    $price = $unitCost + $ProductPrice['ProductPrice']['add_on'];
                                }
                                mysql_query("UPDATE products SET unit_price = ".$price." WHERE id = ".$this->data['ProductPrice']['product_id']);
                            }
                            $k++;
                        }
                    }
                }
            }
            // Save User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Save Set Price', $id);
            echo MESSAGE_DATA_HAS_BEEN_SAVED;
            exit;
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Set Price', $id);
        $branches = ClassRegistry::init('Branch')->find('all',
                    array(
                        'joins' => array(
                            array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')),
                            array('table' => 'product_branches', 'type' => 'inner', 'conditions' => array('product_branches.branch_id=Branch.id'))
                        ),
                        'fields' => array('Branch.id', 'Branch.name'),
                        'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'], 'product_branches.product_id=' . $id)));
        $products = $this->Product->read(null, $id);
        $this->set(compact('branches', 'products'));
    }
    
    function productPriceDetail($branchId, $id){
        $this->layout = 'ajax';
        if(empty($id) && $branchId < 0){
            exit;
        }
        if($branchId == 0){
            $branchSym = 1;
        } else {
            $branchSym = $branchId;
        }
        $currency = mysql_query("SELECT symbol FROM currency_centers WHERE id = (SELECT currency_id FROM branches WHERE id = ".$branchSym." LIMIT 1)");
        $rowCurr  = mysql_fetch_array($currency);
        $symbol   = $rowCurr[0];
        $products = $this->Product->read(null, $id);
        $branch   = ClassRegistry::init('Branch')->read(null, $branchId);
        $this->set(compact('products', 'branchId', 'symbol', 'branch'));
    }

    function getSkuUom($uomId = null) {
        $this->layout = 'ajax';
        if ($uomId != null) {
            $this->set('uomId', $uomId);
        } else {
            echo "Error Select Uom";
        }
    }

    function checkSkuUom($company_id = null, $sku = null) {
        $this->layout = 'ajax';
        if ($sku != null) {
            $conditions = "OR pws.sku = '" . $sku . "') AND p.company_id = ".$company_id." AND p.is_active = 1";
            if ($this->Helper->checkDouplicateSku('p.code', 'products AS p', $sku, $conditions, "LEFT JOIN product_with_skus as pws ON pws.product_id = p.id")) {
                $result = 'available';
            } else {
                $result = 'not available';
            }
            echo $result;
        } else {
            echo "Error Sku";
        }
        exit;
    }

    function checkPuc($company_id = null, $puc = null) {
        $this->layout = 'ajax';
        if ($puc != null) {
            if ($this->Helper->checkDouplicate('barcode', 'products', $puc, "company_id=".$company_id." AND is_active = 1")) {
                $result = 'available';
            } else {
                $result = 'not available';
            }
            echo $result;
        } else {
            echo "Error UPC";
        }
        exit;
    }

    function checkSkuUomEdit($company_id = null, $sku = null, $product_id = "", $pSkuId = "") {
        $this->layout = 'ajax';
        if ($sku != null) {
            $compareId = "";
            if (!empty($product_id)) {
                $compareId = "p.id <> " . $product_id . " AND";
            }
            if (!empty($pSkuId)) {
                $conditions = "p.company_id = ".$company_id." AND p.is_active = 1 OR pws.sku = '" . $sku . "' AND pws.id <> " . $pSkuId;
            } else {
                $conditions = "p.company_id = ".$company_id." AND p.is_active = 1 OR pws.sku = '" . $sku . "'";
            }
            $join = "LEFT JOIN product_with_skus as pws ON pws.product_id = p.id";
            if ($this->Helper->checkDouplicateEditOther('p.code', 'products AS p', $compareId, $sku, $conditions, $join)) {
                $result = 'available';
            } else {
                $result = 'not available';
            }
            echo $result;
        } else {
            echo "Error Sku";
        }
        exit;
    }

    function checkPucEdit($company_id = null, $puc = null, $product_id = null) {
        $this->layout = 'ajax';
        if ($puc != null && $product_id != null) {
            if ($this->Helper->checkDouplicateEdit('barcode', 'products', $product_id, $puc, "company_id=".$company_id." AND is_active = 1")) {
                $result = 'available';
            } else {
                $result = 'not available';
            }
            echo $result;
        } else {
            echo "Error UPC";
        }
        exit;
    }
    
    function setExpired(){
        $this->layout = 'ajax';
    }
    
    function setProductPacket(){
        $this->layout = 'ajax';
    }
    
    function exportExcel($typeShow = null, $department = null, $category = null, $subProduct = null, $subSubProduct = null){
        $this->layout = 'ajax';
        if (isset($_POST['action']) && $_POST['action'] == 'export') {
            $user = $this->getCurrentUser();
            $allowViewCost = $this->Helper->checkAccess($user['User']['id'], $this->params['controller'], 'viewCost');
            $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Export to Excel');
            $filename = "public/report/product_export.csv";
            $fp = fopen($filename, "wb");
            $titlePriceType = '';
            $cmtPriceType   = "SELECT price_types.id, price_types.name FROM price_types WHERE price_types.is_active = 1 AND price_types.id > 1 ORDER BY price_types.ordering ASC";
            $sqlPriceType   = mysql_query($cmtPriceType);
            if(mysql_num_rows($sqlPriceType)){
                while($rowPriceType = mysql_fetch_array($sqlPriceType)){
                    $titlePriceType .= "\t".$rowPriceType[1];
                }
            }
            $fieldCost = "";
            if($allowViewCost){
                $fieldCost = "\t".TABLE_UNIT_COST;
            }
            $excelContent  = 'Products' . "\n\n";
            $excelContent .= TABLE_PRODUCT_NAME . "\t" . MENU_DEPARTMENT. "\t" . MENU_PRODUCT_GROUP_MANAGEMENT. "\t" . TABLE_SUB_OF_GROUP . "\t" . "Sub-Sub Catergory" . "\t" . TABLE_COUNTRY. "\t" . TABLE_BRAND. "\t" .TABLE_VENDOR. "\t " .TABLE_VENDOR. " 2\t " .TABLE_BARCODE . "\t" . TABLE_UOM . "\t" . "Cost \t Sell \t GP% \t GP$" ;
            $query  = mysql_query('SELECT 
                                            p.id, 
                                            p.name, 
                                            pgroups.id AS pg_id, 
                                            pgroups.name AS pg_name, 
                                            (SELECT name FROM pgroups AS p WHERE p.id = pgroups.parent_id LIMIT 01) AS sub_category_name,
                                            (SELECT (SELECT name FROM pgroups AS s WHERE s.id = p.parent_id LIMIT 01) FROM pgroups AS p WHERE p.id = pgroups.parent_id LIMIT 01) AS category_name, 
                                            p.barcode, 
                                            uoms.name AS 
                                            uom_name, 
                                            brands.name AS b_name, 
                                            IFNULL(p.default_cost, p.unit_cost) AS unit_cost, 
                                            countries.name AS country_name,
                                            departments.name AS department_name,
                                            (SELECT amount FROM product_prices WHERE product_prices.product_id = p.id AND product_prices.price_type_id = 3 AND product_prices.uom_id = uoms.id LIMIT 01) AS price_amount
                                          FROM 
                                            products AS p 
                                          LEFT JOIN 
                                            uoms ON uoms.id = p.price_uom_id
                                          INNER JOIN 
                                            product_pgroups ON product_pgroups.product_id = p.id
                                          LEFT JOIN 
                                            pgroups ON pgroups.id = product_pgroups.pgroup_id
                                          LEFT JOIN 
                                            brands ON brands.id = p.brand_id
                                          LEFT JOIN 
                                            countries ON countries.id = p.country_id
                                          LEFT JOIN 
                                            departments ON departments.id = pgroups.department_id
                                          WHERE 
                                            p.is_active=1 '.$condition.'
                                        ORDER BY 
                                            departments.name, p.name ASC');
            $index = 1;
            while ($data = mysql_fetch_array($query)) {
                $unitCost  = $data['unit_cost']>0?$data['unit_cost']:0;
                
                $vendor  = '';
                $vendor2 = '';
                $sqlV = mysql_query("SELECT name FROM vendors WHERE id IN (SELECT vendor_id FROM product_vendors WHERE product_id = ".$data['id'].")");
                $v = 0;
                while($rowV = mysql_fetch_array($sqlV)){
                    if($v == 0){
                        $vendor = $rowV['name'];
                    } else {
                        $vendor2 = $rowV['name'];
                    }
                    $v++;
                }
                
                // Vendor
                $gpPer = (($data['price_amount'] - $unitCost) / $data['price_amount']) * 100;
                $gpAmt = $data['price_amount'] - $unitCost;
                $excelContent .= "\n" . 
                                $data['name'] . "\t" . 
                                $data['department_name']. "\t" . 
                                $data['category_name'] . "\t" . 
                                $data['sub_category_name']. "\t" . 
                                $data['pg_name']. "\t" . 
                                $data['country_name']. "\t" . 
                                $data['b_name'] ."\t". 
                                $vendor. "\t" .
                                $vendor2. "\t'" . 
                                $data['barcode']. "\t" . 
                                $data['uom_name']. "\t" . 
                                $unitCost . "\t" . 
                                $data['price_amount'] . "\t" . 
                                $gpPer. "\t" . 
                                $gpAmt;
            }
            $index = 1;
            $excelContent .= "\n\nServices" . "\n\n";
            $excelContent .= TABLE_NO . "\t" . TABLE_NAME. "\t" . MENU_PRODUCT_GROUP_MANAGEMENT. "\t" . TABLE_BARCODE. "\t" . TABLE_UOM. "\t" .TABLE_PRICE. "\t " .GENERAL_DESCRIPTION;
            $sqlService   = mysql_query("SELECT services.id, services.name, pgroups.name AS pg_name, services.code, uoms.name AS uom_name, services.unit_price, services.description FROM services LEFT JOIN pgroups ON pgroups.id = services.section_id LEFT JOIN uoms ON uoms.id = services.uom_id WHERE services.is_active = 1 ORDER BY services.code");
            while($rowService = mysql_fetch_array($sqlService)){
                $excelContent .= "\n" . $index++ . "\t" . $rowService['name']. "\t" . $rowService['pg_name']. "\t" . $rowService['code']. "\t" . $rowService['uom_name']. "\t" . $rowService['unit_price']. "\t" . $rowService['description'];
            }
            $excelContent = chr(255) . chr(254) . @mb_convert_encoding($excelContent, 'UTF-16LE', 'UTF-8');
            fwrite($fp, $excelContent);
            fclose($fp);
            exit();
        }
    }
    
    function setCost(){
        $this->layout = 'ajax';
    }
    
    function setProductWithCustomer($productId, $customerId){
        $this->layout = 'ajax';
        if(!empty($productId) && !empty($customerId) && !empty($this->data)){
            if($this->data['name'] != ''){
                $user = $this->getCurrentUser();
                $productName = mysql_real_escape_string($this->data['name']);
                mysql_query("INSERT INTO `product_with_customers` (`product_id`, `customer_id`, `name`, `created`, `created_by`) VALUES (".$productId.", ".$customerId.", '".$productName."', '".date("Y-m-d H:i:s")."', ".$user['User']['id'].")
                             ON DUPLICATE KEY UPDATE `created`='".date("Y-m-d H:i:s")."';");
            }
        }
        exit;
    }
    
    function cloneProductInfo($id){
        $this->layout = 'ajax';
        $clone = array();
        $user = $this->getCurrentUser();
        if (!$id) {
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Clone Invalid Product Id', $id);
            $clone['error'] = 1;
            echo json_encode($clone);
            exit;
        }
        $this->data = $this->Product->read(null, $id);
        if(empty($this->data)){
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Clone Invalid Product', $id);
            $clone['error'] = 2;
            echo json_encode($clone);
            exit;
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Clone', $id);
        // Product Information
        $clone['error'] = 0;
        $clone['Product']['is_expired_date'] = $this->data['Product']['is_expired_date'];
        $clone['Product']['pgroup_id'] = '';
        $clone['Product']['name']  = $this->data['Product']['name'];
        $clone['Product']['color'] = $this->data['Product']['color'];
        $clone['Product']['photo'] = $this->data['Product']['photo'];
        $clone['Product']['price_uom_id'] = $this->data['Product']['price_uom_id'];
        $clone['Product']['is_not_for_sale'] = $this->data['Product']['is_not_for_sale'];
        $clone['Product']['reorder_level'] = $this->data['Product']['reorder_level'];
        $clone['Product']['spec'] = $this->data['Product']['spec'];
        $clone['Product']['description'] = $this->data['Product']['description'];
        $clone['Product']['width'] = $this->data['Product']['width'];
        $clone['Product']['height'] = $this->data['Product']['height'];
        $clone['Product']['length'] = $this->data['Product']['length'];
        $clone['Product']['size_uom_id'] = $this->data['Product']['size_uom_id'];
        $clone['Product']['cubic_meter'] = $this->data['Product']['cubic_meter'];
        $clone['Product']['weight'] = $this->data['Product']['weight'];
        $clone['Product']['weight_uom_id'] = $this->data['Product']['weight_uom_id'];
        $clone['Product']['period_from'] = '';
        $clone['Product']['period_to'] = '';
        if($this->data['Product']['period_from'] != '' && $this->data['Product']['period_from'] != '0000-00-00'){
            $clone['Product']['period_from'] = $this->Helper->dateShort($this->data['Product']['period_from']);
        }
        if($this->data['Product']['period_to'] != '' && $this->data['Product']['period_to'] != '0000-00-00'){
            $clone['Product']['period_to'] = $this->Helper->dateShort($this->data['Product']['period_to']);
        }
        // ICS
        $ics = mysql_query("SELECT * FROM accounts WHERE product_id = ".$id);
        if(mysql_num_rows($ics)){
            while($rowIcs = mysql_fetch_array($ics)){
                if($rowIcs['account_type_id'] == 1){
                    $clone['Product']['ics_inv'] = $rowIcs['chart_account_id'];
                } else if($rowIcs['account_type_id'] == 2){
                    $clone['Product']['ics_cogs'] = $rowIcs['chart_account_id'];
                } else {
                    $clone['Product']['ics_sales'] = $rowIcs['chart_account_id'];
                }
            }
        } else {
            $clone['Product']['ics_inv'] = '';
            $clone['Product']['ics_cogs'] = '';
            $clone['Product']['ics_sales'] = '';
        }
        $pgroup = mysql_query("SELECT pgroup_id FROM product_pgroups WHERE product_id = ".$id." LIMIT 1;");
        if(mysql_num_rows($pgroup)){
            $rowPgroup = mysql_fetch_array($pgroup);
            $clone['Product']['pgroup_id'] = $rowPgroup[0];
        }
        echo json_encode($clone);
        exit;
    }
    
    function viewTotalCostPrice(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = 485 AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = 485;
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 5;
            $this->UserDashboard->save($userDash);
        }
    }
    
    function viewChangeCost(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = 486 AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = 486;
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 5;
            $this->UserDashboard->save($userDash);
        }
    }
    
    function resultChangeCost(){
        $this->layout = 'ajax';
        $dateNow = date("Y-m-d");
        $content = array();
        $result  = '';
        $sqlHis = mysql_query("SELECT products.code, products.name, product_unit_cost_histories.old_cost, product_unit_cost_histories.new_cost FROM product_unit_cost_histories INNER JOIN products ON products.id = product_unit_cost_histories.product_id WHERE DATE(product_unit_cost_histories.created) = '".$dateNow."' ORDER BY product_unit_cost_histories.created DESC LIMIT 15");
        if(mysql_num_rows($sqlHis)){
            $index = 1;
            $symbol = '';
            if($rowHis['new_cost'] > $rowHis['old_cost']){
                $img = 'up.png';
                $color = 'color: #0a0;';
            } else if($rowHis['old_cost'] > $rowHis['new_cost']){
                $img = 'down.png';
                $color = 'color: red;';
            } else {
                $img = '';
                $color = '';
            }
            if($img != ''){
                $symbol = '<img src="' . $this->webroot . 'img/button/'.$img.'" style="margin-left: 5px;" />';
            }
            while($rowHis = mysql_fetch_array($sqlHis)){
                $result .= '<tr>';
                $result .= '<td class="first">'.$index.'</td>';
                $result .= '<td>'.$rowHis['code'].'</td>';
                $result .= '<td>'.$rowHis['name'].'</td>';
                $result .= '<td>'.number_format($rowHis['old_cost'], 2).'</td>';
                $result .= '<td style="'.$color.'">'.number_format($rowHis['new_cost'], 2).$symbol.'</td>';
                $result .= '</tr>';
            }
        } else {
            $result .= '<td colspan="5" class="first">'.TABLE_NO_RECORD.'</td>';
        }
        $content['update'] = date("d/m/Y H:i:s");
        $content['result'] = $result;
        echo json_encode($result);
        exit;
    }
    
    function viewProductHistory($productId = null){
        $this->layout = 'ajax';
        if (!$productId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'View Product History', $productId);             
        $this->set(compact('productId'));
    }
    
    function viewProductReorderLevel(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = 491 AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $queryProductReorderLevelPer = mysql_query("SELECT id FROM modules WHERE name = 'Products Reorder Level' LIMIT 01");
            $rowProductReorderLevelPer   = mysql_fetch_array($queryProductReorderLevelPer);
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = $rowProductReorderLevelPer[0];
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 5;
            $this->UserDashboard->save($userDash);
        }
    }
    
    function viewProductReorderLevelAjax(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
    }
    
    function viewProductExpireDate(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // Check Module Exist
        $sqlDash = mysql_query("SELECT id FROM user_dashboards WHERE module_id = (SELECT id FROM modules WHERE name = 'Products Expire Date') AND user_id = {$user['User']['id']} LIMIT 1");
        if(!mysql_num_rows($sqlDash)){
            $queryProductExpireDatePer = mysql_query("SELECT id FROM modules WHERE name = 'Products Expire Date' LIMIT 01");
            $rowProductExpireDatePer   = mysql_fetch_array($queryProductExpireDatePer);
            $this->loadModel('UserDashboard');
            $userDash = array();
            $userDash['UserDashboard']['user_id']      = $user['User']['id'];
            $userDash['UserDashboard']['module_id']    = $rowProductExpireDatePer[0];
            $userDash['UserDashboard']['display']      = 1;
            $userDash['UserDashboard']['auto_refresh'] = 1;
            $userDash['UserDashboard']['time_refresh'] = 5;
            $this->UserDashboard->save($userDash);
        }
    }
    
    function viewProductExpireDateAjax(){
        $this->layout = 'ajax';
    }
    
    function printBarcodeTmp1($item = null, $filter, $category){
        $this->layout = 'ajax';
        $product = '';
        $printAll = 0;
        if(!empty($item) && $item != 'all'){
            $product = ClassRegistry::init('Product')->find('first', array('conditions' => array('is_active' => 1, 'id' => $item)));
        } else if($item == 'all' && $filter == 2) {
            $printAll = 1;
            if($category != 'all'){
                $joinProductgroup  = array(
                             'table' => 'product_pgroups',
                             'type' => 'INNER',
                             'alias' => 'ProductPgroup',
                             'conditions' => array('ProductPgroup.product_id = Product.id', 'ProductPgroup.pgroup_id' => $category));
                $product = ClassRegistry::init('Product')->find('all', array('conditions' => array('is_active' => 1), 'joins' => array($joinProductgroup)));
            } else {
                $product = ClassRegistry::init('Product')->find('all', array('conditions' => array('is_active' => 1)));
            }
        }
        $this->set(compact('product', 'printAll'));
    }
    
    function printBarcodeTmp2($item = null, $filter, $category){
        $this->layout = 'ajax';
        $product = '';
        $printAll = 0;
        if(!empty($item) && $item != 'all'){
            $product = ClassRegistry::init('Product')->find('first', array('conditions' => array('is_active' => 1, 'id' => $item)));
        } else if($item == 'all' && $filter == 2) {
            $printAll = 1;
            if($category != 'all'){
                $joinProductgroup  = array(
                             'table' => 'product_pgroups',
                             'type' => 'INNER',
                             'alias' => 'ProductPgroup',
                             'conditions' => array('ProductPgroup.product_id = Product.id', 'ProductPgroup.pgroup_id' => $category));
                $product = ClassRegistry::init('Product')->find('all', array('conditions' => array('is_active' => 1), 'joins' => array($joinProductgroup)));
            } else {
                $product = ClassRegistry::init('Product')->find('all', array('conditions' => array('is_active' => 1)));
            }
        }
        $this->set(compact('product', 'printAll'));
    }
    
    function printBarcodeTmp3($item = null, $filter, $category){
        $this->layout = 'ajax';
        $product = '';
        $printAll = 0;
        if(!empty($item) && $item != 'all'){
            $product = ClassRegistry::init('Product')->find('first', array('conditions' => array('is_active' => 1, 'id' => $item)));
        } else if($item == 'all' && $filter == 2) {
            $printAll = 1;
            if($category != 'all'){
                $joinProductgroup  = array(
                             'table' => 'product_pgroups',
                             'type' => 'INNER',
                             'alias' => 'ProductPgroup',
                             'conditions' => array('ProductPgroup.product_id = Product.id', 'ProductPgroup.pgroup_id' => $category));
                $product = ClassRegistry::init('Product')->find('all', array('conditions' => array('is_active' => 1), 'joins' => array($joinProductgroup)));
            } else {
                $product = ClassRegistry::init('Product')->find('all', array('conditions' => array('is_active' => 1)));
            }
        }
        $this->set(compact('product', 'printAll'));
    }
    
    function printProductByCheck($pId = null, $save = null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!$pId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if($pId == "clearData"){
            mysql_query("DELETE FROM `user_print_product` WHERE user_id = ".$user['User']['id']."");
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
        }else{
            mysql_query("DELETE FROM `user_print_product` WHERE user_id = ".$user['User']['id']." AND product_id = ".$pId."");
            if($save == 0){
                mysql_query("INSERT INTO `user_print_product`(`user_id`, `product_id`) VALUES (".$user['User']['id'].", ".$pId.")");
            }
        }
        exit;
    }
    
    function printByUomBarcode($proId = null){
        $this->layout = 'ajax';
        if (!$proId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $selectOption = "<select id='proUomSku' style='width: 214px;'>";
        
        //Main Uom
        $queryProMainUom = mysql_query("SELECT price_uom_id, (SELECT name FROM uoms WHERE id = price_uom_id), code FROM products WHERE id = {$proId} LIMIT 01");
        $dataProMainUom  = mysql_fetch_array($queryProMainUom);
        
        //Pro With Sku
        $queryProUomSku = mysql_query("SELECT uom_id, (SELECT name FROM uoms WHERE id = uom_id), sku FROM product_with_skus WHERE product_id = {$proId} ORDER BY sku");
        if(mysql_num_rows($queryProUomSku) > 0){
            while($dataProUomSku = mysql_fetch_array($queryProUomSku)){
                $selectOption .= "<option vlaue='".$dataProUomSku[0]."' sku-name='".$dataProUomSku[2]."'>".$dataProUomSku[1]."</option>";
            }
        }else{
            $selectOption .= "<option vlaue='".$dataProMainUom[0]."' sku-name='".$dataProMainUom[2]."'>".$dataProMainUom[1]."</option>";
        }
        $selectOption .= "</select>";
        echo $selectOption;
        exit();
    }
    
    function viewActivityByGraph($productId = null, $dateRange = null, $group = null, $chart = null){
        $this->layout = 'ajax';
        if(empty($productId) || empty($dateRange) || empty($group) || empty($chart)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->set(compact('dateRange', 'group', 'chart', 'productId'));
    }
    
    function viewPurchaseSalesByGraph($productId = null, $dateRange = null, $group = null, $chart = null){
        $this->layout = 'ajax';
        if(empty($productId) || empty($dateRange) || empty($group) || empty($chart)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->set(compact('dateRange', 'group', 'chart', 'productId'));
    }
    
    function addPgroup(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Pgroup');
            $result   = array();
            $comCheck = 0;
            if(!empty($this->data['Pgroup']['company_id'])){
                if(is_array($this->data['Pgroup']['company_id'])){
                    $comCheck = implode(",", $this->data['Pgroup']['company_id']);
                } else {
                    $comCheck = $this->data['Pgroup']['company_id'];
                }
            }
            if ($this->Helper->checkDouplicate('name', 'pgroups', $this->data['Pgroup']['name'], 'is_active = 1 AND id IN (SELECT pgroup_id FROM pgroup_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Product Group', 'Save Quick Add New (Name ready existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
//                $r = 0;
//                $e = 0;
//                $syncEco   = array();
//                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->Pgroup->create();
                $this->data['Pgroup']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Pgroup']['created']    = $dateNow;
                $this->data['Pgroup']['created_by'] = $user['User']['id'];
                $this->data['Pgroup']['is_active']  = 1;
                if ($this->Pgroup->save($this->data)) {
                    $pgroupId = $this->Pgroup->id;
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Pgroup'], 'pgroups');
//                    $restCode[$r]['modified']   = $dateNow;
//                    $restCode[$r]['dbtodo']     = 'pgroups';
//                    $restCode[$r]['actodo']     = 'is';
//                    $r++;
                    // Pgroup Company
                    if (!empty($this->data['Pgroup']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Pgroup']['company_id']); $i++) {
                            mysql_query("INSERT INTO pgroup_companies (pgroup_id, company_id) VALUES ('" . $pgroupId . "','" . $this->data['Pgroup']['company_id'][$i] . "')");
                            // Convert to REST
//                            $restCode[$r]['pgroup_id']  = $this->data['Pgroup']['sys_code'];
//                            $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['Pgroup']['company_id'][$i]);
//                            $restCode[$r]['dbtodo']     = 'pgroup_companies';
//                            $restCode[$r]['actodo']     = 'is';
//                            $r++;
                        }
                    }
                    // Send to E-Commerce
                    // Convert to REST
//                    $syncEco[$e]['sys_code']  = $this->data['Pgroup']['sys_code'];
//                    $syncEco[$e]['name']      = $this->data['Pgroup']['name'];
//                    $syncEco[$e]['status']    = 2;
//                    $syncEco[$e]['created']   = $dateNow;
//                    $syncEco[$e]['dbtodo']    = 'pgroups';
//                    $syncEco[$e]['actodo']    = 'is';
//                    $e++;
                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save File Send to E-Commerce
//                    $this->Helper->sendFileToSyncPublic($syncEco);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Product Group', 'Save Quick Add New', $pgroupId);
                    $result['error']  = 0;
                    $result['option'] = '<option value="">'.INPUT_SELECT.'</option>';
                    $pgroups = ClassRegistry::init('Pgroup')->find('all', array('order' => 'name', 'conditions' => array('is_active' => 1)));
                    foreach($pgroups AS $pgroup){
                        $selected = '';
                        if($pgroup['Pgroup']['id'] == $pgroupId){
                            $selected = 'selected="selected"';
                        }
                        $result['option'] .= '<option value="'.$pgroup['Pgroup']['id'].'" '.$selected.'>'.$pgroup['Pgroup']['name'].'</option>';
                    }
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Product Group', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Product Group', 'Quick Add New');
        $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $this->set(compact("companies"));
    }
    
    function addUom(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Uom');
            $result = array();
            if ($this->Helper->checkDouplicate('name', 'uoms', $this->data['Uom']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Save Quick Add New (Name has existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
                Configure::write('debug', 0);
//                $r = 0;
//                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->Uom->create();
                $this->data['Uom']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Uom']['created']    = $dateNow;
                $this->data['Uom']['created_by'] = $user['User']['id'];
                $this->data['Uom']['is_active'] = 1;
                if ($this->Uom->save($this->data)) {
                    $error = mysql_error();
                    if($error != 'Invalid Data'){
                        $uomId = $this->Uom->id;
                        // Convert to REST
//                        $restCode[$r] = $this->Helper->convertToDataSync($this->data['Uom'], 'uoms');
//                        $restCode[$r]['modified'] = $dateNow;
//                        $restCode[$r]['dbtodo']   = 'uoms';
//                        $restCode[$r]['actodo']   = 'is';
//                        $r++;
                        // Send to E-Commerce
                        $e = 0;
                        $syncEco = array();
                        // Convert to REST
//                        $syncEco[$e]['sys_code']  = $this->data['Uom']['sys_code'];
//                        $syncEco[$e]['name']      = $this->data['Uom']['name'];
//                        $syncEco[$e]['abbr']      = $this->data['Uom']['abbr'];
//                        $syncEco[$e]['created']   = $dateNow;
//                        $syncEco[$e]['dbtodo']    = 'uoms';
//                        $syncEco[$e]['actodo']    = 'is';
                        // Save File Send to E-Commerce
//                        $this->Helper->sendFileToSyncPublic($syncEco);
                        // UoM Conversion
                        if(!empty($this->data['UomConversion']['to_uom_id'])){
                            $this->loadModel('UomConversion');
                            $this->UomConversion->create();
                            $this->data['UomConversion']['from_uom_id'] = $uomId;
                            $this->data['UomConversion']['to_uom_id']   = $this->data['UomConversion']['to_uom_id'];
                            $this->data['UomConversion']['value']       = $this->Helper->replaceThousand($this->data['UomConversion']['value']);
                            $this->data['UomConversion']['created']     = $dateNow;
                            $this->data['UomConversion']['created_by']  = $user['User']['id'];
                            $this->data['UomConversion']['is_active']   = 1;
                            $this->data['UomConversion']['is_small_uom'] = 1;
                            if ($this->UomConversion->save($this->data)) {
                                $error = mysql_error();
                                if($error != 'Invalid Data'){
                                    // Convert to REST
//                                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['UomConversion'], 'uom_conversions');
//                                    $restCode[$r]['modified']   = $dateNow;
//                                    $restCode[$r]['dbtodo']     = 'uom_conversions';
//                                    $restCode[$r]['actodo']     = 'is';
//                                    $r++;
                                    if(!empty($this->data['other_uom'])){
                                        for($i = 0; $i < sizeof($this->data['other_uom']); $i++){
                                            $checkVal = abs($this->data['UomConversion']['value'] % $this->data['other_value'][$i]);
                                            if($this->data['other_value'][$i] > 0 && $this->data['other_value'][$i] != '' && $checkVal == 0 && ($this->data['other_value'][$i] <= $this->data['UomConversion']['value'])){
                                                $this->UomConversion->create();
                                                $otherUom = array();
                                                $otherUom['UomConversion']['from_uom_id'] = $uomId;
                                                $otherUom['UomConversion']['to_uom_id']   = $this->data['other_uom'][$i];
                                                $otherUom['UomConversion']['value']       = $this->Helper->replaceThousand($this->data['other_value'][$i]);
                                                $otherUom['UomConversion']['created']     = $dateNow;
                                                $otherUom['UomConversion']['created_by']  = $user['User']['id'];
                                                $otherUom['UomConversion']['is_active']   = 1;
                                                $this->UomConversion->saveAll($otherUom);
                                                // Convert to REST
//                                                $restCode[$r] = $this->Helper->convertToDataSync($otherUom['UomConversion'], 'uom_conversions');
//                                                $restCode[$r]['modified']   = $dateNow;
//                                                $restCode[$r]['dbtodo']     = 'uom_conversions';
//                                                $restCode[$r]['actodo']     = 'is';
//                                                $r++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        // Save File Send
//                        $this->Helper->sendFileToSync($restCode, 0, 0);
                        // Save User Activity
                        $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Save Quick Add New', $uomId);
                        $result['error']  = 0;
                        $result['option'] = '<option value="">'.INPUT_SELECT.'</option>';
                        $uoms = ClassRegistry::init('Uom')->find('all', array('order' => 'name', 'conditions' => array('is_active' => 1)));
                        foreach($uoms AS $uom){
                            $selected = '';
                            if($uom['Uom']['id'] == $uomId){
                                $selected = 'selected="selected"';
                            }
                            $result['option'] .= '<option value="'.$uom['Uom']['id'].'" '.$selected.'>'.$uom['Uom']['name'].'</option>';
                        }
                        echo json_encode($result);
                        exit;
                    } else {
                        $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Save Quick Add New (Error '.$error.')');
                        $result['error'] = 1;
                        echo json_encode($result);
                        exit;
                    }
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'UoM', 'Quick Add New');
        $types = array(
            'Count' => 'Count',
            'Weight' => 'Weight',
            'Length' => 'Length',
            'Area' => 'Area',
            'Volume' => 'Volume',
            'Time' => 'Time'
        );
        $uomList = ClassRegistry::init('Uom')->find('list', array('conditions' => array('is_active != 2', 'Uom.id NOT IN (SELECT from_uom_id FROM `uom_conversions` WHERE is_active = 1)')));
        $this->set(compact("types", "uomList"));
    }
    
    function addBrand(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Brand');
            $result   = array();
            if ($this->Helper->checkDouplicate('name', 'brands', $this->data['Brand']['name'], 'is_active = 1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Save Quick Add New (Name ready existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
//                $r = 0;
//                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->Brand->create();
                $this->data['Brand']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Brand']['created']    = $dateNow;
                $this->data['Brand']['created_by'] = $user['User']['id'];
                $this->data['Brand']['is_active']  = 1;
                if ($this->Brand->save($this->data)) {
                    $brandId = $this->Brand->id;
                    // Convert to REST
//                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Brand'], 'brands');
//                    $restCode[$r]['modified']   = $dateNow;
//                    $restCode[$r]['dbtodo']     = 'brands';
//                    $restCode[$r]['actodo']     = 'is';
//                    $r++;
                    // Save File Send
//                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Save Quick Add New', $brandId);
                    $result['error']  = 0;
                    $result['option'] = '<option value="">'.INPUT_SELECT.'</option>';
                    $brands = ClassRegistry::init('Brand')->find('all', array('order' => 'name', 'conditions' => array('is_active' => 1)));
                    foreach($brands AS $brand){
                        $selected = '';
                        if($brand['Brand']['id'] == $brandId){
                            $selected = 'selected="selected"';
                        }
                        $result['option'] .= '<option value="'.$brand['Brand']['id'].'" '.$selected.'>'.$brand['Brand']['name'].'</option>';
                    }
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Product Group', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Brand', 'Quick Add New');
    }
    
    function addCountry(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Country');
            $result   = array();
            if ($this->Helper->checkDouplicate('name', 'countries', $this->data['Country']['name'], 'is_active = 1')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Country', 'Save Quick Add New (Name ready existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
                $this->Country->create();
                $this->data['Country']['is_active']  = 1;
                if ($this->Country->save($this->data)) {
                    $countryId = $this->Country->id;
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Country', 'Save Quick Add New', $countryId);
                    $result['error']  = 0;
                    $result['option'] = '<option value="">'.INPUT_SELECT.'</option>';
                    $countries = ClassRegistry::init('Country')->find('all', array('order' => 'name', 'conditions' => array('is_active' => 1)));
                    foreach($countries AS $country){
                        $selected = '';
                        if($country['Country']['id'] == $countryId){
                            $selected = 'selected="selected"';
                        }
                        $result['option'] .= '<option value="'.$country['Country']['id'].'" '.$selected.'>'.$country['Country']['name'].'</option>';
                    }
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Country', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Country', 'Quick Add New');
    }
    
    function quickAdd() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->Product->create();
            $dateNow  = date("Y-m-d H:i:s");
            $smValUom = ClassRegistry::init('UomConversion')->find('first', array('fileds' => array('value'), 'order' => 'id', 'conditions' => array('from_uom_id' => $this->data['Product']['price_uom_id'], 'is_small_uom = 1', 'is_active' => 1)));
            if (!empty($smValUom)) {
                $this->data['Product']['small_val_uom'] = $smValUom['UomConversion']['value'];
            } else {
                $this->data['Product']['small_val_uom'] = 1;
            }
            if($this->data['Product']['barcode'] == ""){
                $this->data['Product']['barcode'] = 'P';
            }
            $unitCost = $this->data['Product']['unit_cost'] != "" ? str_replace(",", "", $this->data['Product']['unit_cost']) : 0;
            $this->data['Product']['sys_code']        = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['Product']['default_cost']    = $unitCost;
            $this->data['Product']['unit_cost']       = $unitCost;
            $this->data['Product']['code']            = $this->data['Product']['barcode'];
            $this->data['Product']['reorder_level']   = 0;
            $this->data['Product']['created']         = $dateNow;
            $this->data['Product']['created_by']      = $user['User']['id'];
            $this->data['Product']['is_active']       = 1;
            if ($this->Product->save($this->data)) {
                $lastInsertId = $this->Product->id;
                // product main photo
                if ($this->data['Product']['photo'] != '') {
                    $ext = pathinfo($this->data['Product']['photo'], PATHINFO_EXTENSION);
                    $photoName =  $lastInsertId . '_' . md5($this->data['Product']['photo']).".".$ext;
                    rename('public/product_photo/tmp/' . $this->data['Product']['photo'], 'public/product_photo/' . $photoName);
                    rename('public/product_photo/tmp/thumbnail/' . $this->data['Product']['photo'], 'public/product_photo/tmp/thumbnail/' . $photoName);
                    mysql_query("UPDATE products SET photo='" . $photoName . "' WHERE id=" . $lastInsertId);
                    $this->data['Product']['photo'] = $photoName;
                }
                // Check Product Group Share
                $checkShare = 2;
                if (!empty($this->data['Product']['pgroup_id'])) {
                    $sqlShare = mysql_query("SELECT id FROM e_pgroup_shares WHERE pgroup_id = ".$this->data['Product']['pgroup_id']);
                    if(mysql_num_rows($sqlShare)){
                        $checkShare = 1;
                    }
                }
                if($checkShare == 1){
                    mysql_query("INSERT INTO `e_product_shares` (`company_id`, `product_id`, `created`, `created_by`) VALUES (".$this->data['Product']['company_id'].", ".$lastInsertId.", '".$dateNow."', ".$user['User']['id'].");");
                }
                // product group
                if (!empty($this->data['Product']['pgroup_id'])) {
                    mysql_query("INSERT INTO product_pgroups (product_id, pgroup_id) VALUES ('".$lastInsertId."', '".$this->data['Product']['pgroup_id']."')");
                }
                // SKU of each UOM
                if (!empty($this->data['sku_uom_value'])) {
                    for ($i = 0; $i < sizeof($this->data['sku_uom_value']); $i++) {
                        if ($this->data['sku_uom_value'][$i] != '' && $this->data['sku_uom'][$i] != '') {
                            mysql_query("INSERT INTO product_with_skus (product_id, sku, uom_id) VALUES ('" . $lastInsertId . "', '" . $this->data['sku_uom_value'][$i] . "', '" . $this->data['sku_uom'][$i] . "')");
                        }
                    }
                }
                $branches = ClassRegistry::init('Branch')->find("all", array("conditions" => array("Branch.is_active = 1")));
                foreach($branches AS $branch){
                    mysql_query("INSERT INTO product_branches (product_id,branch_id) VALUES ('" . $lastInsertId . "','" . $branch['Branch']['id'] . "')");
                }
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Save Quick Add New', $lastInsertId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Save Quick Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Quick Add New');
        $companies = ClassRegistry::init('Company')->find('list', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('list', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $pgroups   = ClassRegistry::init('Pgroup')->find('list', array('order' => 'Pgroup.name', 'conditions' => array('Pgroup.is_active' => 1, 'Pgroup.id IN (SELECT pgroup_id FROM pgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].'))')));
        $uoms      = ClassRegistry::init('Uom')->find("list", array("conditions" => array("Uom.is_active = 1"), "order" => "Uom.name"));
        $this->set(compact("companies", "branches", "uoms", "pgroups"));
    }
    
    function viewProductInventory($productId = null){
        $this->layout = 'ajax';
        if (!$productId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $this->set(compact('productId'));
    }
    
    function addColor(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Color');
            $result   = array();
            $dateNow   = date("Y-m-d H:i:s");
            $this->Color->create();
            $this->data['Color']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
            $this->data['Color']['created']    = $dateNow;
            $this->data['Color']['created_by'] = $user['User']['id'];
            $this->data['Color']['is_active']  = 1;
            if ($this->Color->save($this->data)) {
                $colorId = $this->Color->id;
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'Save Quick Add New', $colorId);
                $result['error']  = 0;
                $result['option'] = '<option value="">'.INPUT_SELECT.'</option>';
                $colors = ClassRegistry::init('Color')->find('all', array('order' => 'name', 'conditions' => array('is_active' => 1)));
                foreach($colors AS $color){
                    $selected = '';
                    if($color['Color']['id'] == $colorId){
                        $selected = 'selected="selected"';
                    }
                    $result['option'] .= '<option value="'.$color['Color']['id'].'" '.$selected.'>'.$color['Color']['name'].'</option>';
                }
                echo json_encode($result);
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'Save Quick Add New (Error)');
                $result['error'] = 1;
                echo json_encode($result);
                exit;
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Color', 'Quick Add New');
    }
    
    function addService(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Service');
            $dateNow   = date("Y-m-d H:i:s");
            $this->Service->create();
            $this->data['Service']['sys_code']    = $this->Helper->generateRandomString(4);
            $this->data['Service']['name']        = $this->data['Product']['name'];
            $this->data['Service']['section_id']  = $this->data['Product']['pgroup_id'];
            $this->data['Service']['code']        = $this->data['Product']['code']!=""?$this->data['Product']['code']:"S";
            $this->data['Service']['uom_id']      = $this->data['Product']['uom_id'];
            $this->data['Service']['unit_price']  = $this->data['Product']['unit_price'];
            $this->data['Service']['description'] = $this->data['Product']['description'];
            $this->data['Service']['created']     = $dateNow;
            $this->data['Service']['created_by']  = $user['User']['id'];
            $this->data['Service']['is_active']   = 1;
            if ($this->Service->save($this->data)) {
                $lastInsertId = $this->Service->id;
                // Service Branch
                mysql_query("INSERT INTO service_branches (service_id,branch_id) SELECT " . $lastInsertId . ", id FROM branches WHERE is_active = 1");
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Add New', $this->Service->id);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Add New');
        $pgroups = ClassRegistry::init('Pgroup')->find('list', array('order' => 'Pgroup.name', 'conditions' => array('Pgroup.is_active' => 1, 'Pgroup.id IN (SELECT pgroup_id FROM pgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].'))')));
        $uoms    = ClassRegistry::init('Uom')->find("list", array("conditions" => array("Uom.is_active = 1")));
        $this->set(compact('pgroups', 'uoms'));
    }
    
    function editService($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('code', 'services', $this->data['Product']['id'], $this->data['Product']['code'], 'is_active = 1')) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Edit (Name ready existed)', $id);
                echo MESSAGE_CODE_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $this->loadModel('Service');
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['Service']['id']          = $this->data['Product']['id'];
                $this->data['Service']['name']        = $this->data['Product']['name'];
                $this->data['Service']['section_id']  = $this->data['Product']['pgroup_id'];
                $this->data['Service']['uom_id']      = $this->data['Product']['uom_id'];
                $this->data['Service']['unit_price']  = $this->data['Product']['unit_price'];
                $this->data['Service']['description'] = $this->data['Product']['description'];
                $this->data['Service']['modified']    = $dateNow;
                $this->data['Service']['modified_by'] = $user['User']['id'];
                if ($this->Service->save($this->data)) {
                    // Service Branch
                    mysql_query("DELETE FROM service_branches WHERE service_id=" . $id);
                    mysql_query("INSERT INTO service_branches (service_id,branch_id) SELECT ".$id.", id FROM branches WHERE is_active = 1");
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    // User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->data = ClassRegistry::init('Service')->read(null, $id);
            // User Activity
            $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Edit', $id);
            $pgroups = ClassRegistry::init('Pgroup')->find('list', array('order' => 'Pgroup.name', 'conditions' => array('Pgroup.is_active' => 1, 'Pgroup.id IN (SELECT pgroup_id FROM pgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].'))')));
            $uoms    = ClassRegistry::init('Uom')->find("list", array("conditions" => array("Uom.is_active = 1")));
            $this->set(compact('pgroups', 'uoms'));
        }
    }
    
    function viewService($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'View', $id);
        $this->data = ClassRegistry::init('Service')->read(null, $id);
    }
    
    function deleteService($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        // User Activity
        mysql_query("UPDATE `services` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function saveServicePrice($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $price   = $_POST['price']!=""?$_POST['price']:0;
        $dateNow = date("Y-m-d H:i:s");
        $user    = $this->getCurrentUser();
        // User Activity
        mysql_query("UPDATE `services` SET `unit_price`=".$price.", `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Service', 'Set Price', $id);
        echo MESSAGE_DATA_HAS_BEEN_SAVED;
        exit;
    }
    
    function getCategory($departmentId = null){
        if (!$departmentId) {
            echo '<option value="">'.INPUT_SELECT.'</option>';
            exit;
        }
        $option = '<option value="">'.INPUT_SELECT.'</option>';
        $query = mysql_query("SELECT id, name FROM pgroups WHERE is_active=1 AND parent_id IS NULL AND department_id = ".$departmentId." ORDER BY name");
        while($data=mysql_fetch_array($query)){
            $option .= '<option value="'.$data['id'].'">'.$data['name'].'</option>';
        }
        echo $option;
        exit;
    }
    
    function getSubCategory($categoryId = null){
        if (!$categoryId) {
            echo '<option value="">'.INPUT_SELECT.'</option>';
            exit;
        }
        $option = '<option value="">'.INPUT_SELECT.'</option>';
        $query = mysql_query("SELECT id, name FROM pgroups WHERE parent_id = ".$categoryId." AND is_active = 1 ORDER BY name");
        while($data=mysql_fetch_array($query)){
            $option .= '<option value="'.$data['id'].'">'.$data['name'].'</option>';
        }
        echo $option;
        exit;
    }
}

?>