<?php

class PointOfSalesController extends AppController {
    var $name = 'PointOfSales';
    var $components = array('Helper', 'Inventory');
    var $uses = array("SalesInvoice");

    function getProductByCode($barcode = null, $locationGroupId = null, $company_id = null, $branchId = null, $priceTypeId = null) {
        $this->layout = 'ajax';
        if ($barcode != null && $barcode != '') {
            $result     = "";
            // Check Warehouse Option Allow Negative
            $allowStockMinus = false;
            $warehouseOption = ClassRegistry::init('LocationGroup')->findById($locationGroupId);
            if($warehouseOption['LocationGroup']['allow_negative_stock'] == 1){
                $allowStockMinus = true;
            }
            
            $joinInventory = array('table' => $locationGroupId.'_group_totals', 'type' => 'LEFT', 'alias' => 'InventoryTotal', 'conditions' => array('InventoryTotal.product_id = Product.id', 'InventoryTotal.location_group_id' => $locationGroupId, 'InventoryTotal.total_qty > 0', 'InventoryTotal.location_id IN (SELECT id FROM locations WHERE location_group_id = '.$locationGroupId.' AND is_active = 1)'));
            $joins         = array($joinInventory, array('table' => 'product_with_skus','type' => 'LEFT','alias' => 'ProductWithSku','conditions' => array('ProductWithSku.product_id = Product.id')));
            $product = ClassRegistry::init('Product')->find('first', array(
                    'conditions' => array('Product.is_active' => 1, 'Product.company_id' => $company_id,
                    "OR" => array(
                        'trim(Product.code) = "' . mysql_real_escape_string(trim($barcode)) . '"',
                        'trim(ProductWithSku.sku) = "' . mysql_real_escape_string(trim($barcode)) . '"'
                    )), 'joins' => $joins, 'fields' => array('Product.*', 'IF(COUNT(`ProductWithSku`.id) = 1,`ProductWithSku`.uom_id,"") as sku_uom_id', 'InventoryTotal.total_qty', 'InventoryTotal.total_order'), 'group' => array('Product.id', 'Product.code')));
            if (empty($product)) {
                $product['Product']['id'] = '';
            } else {
                $mainBarcode = $product['Product']['barcode'];
                $product['Product']['barcode'] = $barcode;
                $product['Product']['uom_id']  = $product[0]['sku_uom_id'];
                $product['Product']['packet']  = '';
                // Check Allow Negative Stock
                if($allowStockMinus == true){ // Allow Negative Stock
                    $product['InventoryTotal']['total_qty']   = 1000000;
                    $product['InventoryTotal']['total_order'] = 0;
                }
                // Check Total Qty for get UoM
                if($product['InventoryTotal']['total_qty'] > 0){
                    $query    = mysql_query("SELECT id,name,abbr,1 AS conversion FROM uoms WHERE id=".$product['Product']['price_uom_id']."
                                             UNION
                                             SELECT id,name,abbr,(SELECT value FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$product['Product']['price_uom_id']." AND to_uom_id=uoms.id) AS conversion FROM uoms WHERE id IN (SELECT to_uom_id FROM uom_conversions WHERE is_active=1 AND from_uom_id=".$product['Product']['price_uom_id'].")
                                             ORDER BY conversion ASC");
                    $i = 1;
                    $length = mysql_num_rows($query);
                    while($data=mysql_fetch_array($query)){
                        $priceLbl   = "";
                        $dataItem   = "other";
                        $conversion = $data['conversion'];
                        if($length == $i){ $uom_sm = 1; } else { $uom_sm = 0; }
                        if($data['id'] == $product['Product']['price_uom_id']){ $dataItem = "first"; }
                        $sqlPrice = mysql_query("SELECT price_type_id, amount, percent, add_on, set_type FROM product_prices WHERE product_id =".$product['Product']['id']." AND branch_id =".$branchId." AND uom_id =".$data['id']." AND price_type_id = ".$priceTypeId);
                        if(mysql_num_rows($sqlPrice)){
                            $price = 0;
                            while($rowPrice = mysql_fetch_array($sqlPrice)){
                                $unitCost = $product['Product']['unit_cost'] /  $data['conversion'];
                                if($rowPrice['set_type'] == 1){
                                    $price = $rowPrice['amount'];
                                }else if($rowPrice['set_type'] == 2){
                                    $percent = ($unitCost * $rowPrice['percent']) / 100;
                                    $price = $unitCost + $percent;
                                }else if($rowPrice['set_type'] == 3){
                                    $price = $unitCost + $rowPrice['add_on'];
                                }
                                $priceLbl  = "price='".$price."'";
                            }
                        }else{
                            $priceLbl = "price='0'";
                        }
                        $sqlSku = mysql_query("SELECT sku FROM product_with_skus WHERE product_id = ".$product['Product']['id']." AND uom_id = ".$data['id']." LIMIT 1");
                        if(mysql_num_rows($sqlSku)){
                            $rowSku = mysql_fetch_array($sqlSku);
                            $sku = $rowSku['sku'];
                        } else {
                            $sku = $mainBarcode;
                        }
                        $result .= "<option sku='".$sku."' data-item='{$dataItem}' {$priceLbl} uom-sm='{$uom_sm}' conversion='{$conversion}' value='{$data['id']}'>" . $data['abbr'] . "</option>";
                        $i++;
                    }  
                }
                $product['Product']['uom_list'] = $result;
                // Price By Qty 6 and 12
                $price6  = 0;
                $price12 = 0;
                $sqlQtyP = mysql_query("SELECT * FROM product_qty_prices WHERE product_id = ".$product['Product']['id']);
                if(mysql_num_rows($sqlQtyP)){
                    while($rowQtyP = mysql_fetch_array($sqlQtyP)){
                        if($rowQtyP['qty'] == 6){
                            $price6 = $rowQtyP['unit_price'];
                        } else if($rowQtyP['qty'] == 12){
                            $price12 = $rowQtyP['unit_price'];
                        }
                    }
                }
                $product['Product']['price6']  = $price6;
                $product['Product']['price12'] = $price12;
                // Check Promotional
                $dateOrder   = date("Y-m-d");
                $promotional = array();
                $sqlPromo    = mysql_query("SELECT promotional_details.promotional_id FROM promotional_details 
                INNER JOIN promotionals ON promotionals.id = promotional_details.promotional_id 
                WHERE promotionals.status = 2 AND promotionals.approved IS NOT NULL AND 
                promotionals.approved_by IS NOT NULL AND 
                promotionals.branch_id = ".$branchId." AND 
                promotionals.apply = 1 AND promotionals.start <= '".$dateOrder."' AND 
                promotionals.end >= '".$dateOrder."' AND 
                ((promotionals.customer_id = 1 OR promotionals.cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = 1)) OR (promotionals.customer_id IS NULL AND 
                (promotionals.cgroup_id IS NULL OR promotionals.cgroup_id = ''))) AND 
                promotional_details.product_request_id = ".$product['Product']['id']." 
                ORDER BY promotionals.id DESC LIMIT 1;");
                if(mysql_num_rows($sqlPromo)){
                    $indexP = 0;
                    $rowPromo   = mysql_fetch_array($sqlPromo);
                    $promotional['promotion_id'] = $rowPromo[0];
                    $sqlProduct = mysql_query("SELECT product_request_id,qty_request,uom_request,product_promo_id,qty_promo,uom_promo, discount_percent, discount_amount FROM promotional_details WHERE promotional_id = ".$rowPromo[0]." AND product_request_id = ".$product['Product']['id']);
                    while($rowProduct = mysql_fetch_array($sqlProduct)){
                        $promotional['promotion'][$indexP]['uom_id']              = $rowProduct['uom_request'];
                        $promotional['promotion'][$indexP]['discount_percent']    = $rowProduct['discount_percent'];
                        $promotional['promotion'][$indexP]['discount_amount']     = $rowProduct['discount_amount'];

                        $promotional['promotion'][$indexP]['product_request_id']  = $rowProduct['product_request_id'];
                        $promotional['promotion'][$indexP]['qty_request']         = $rowProduct['qty_request'];
                        $promotional['promotion'][$indexP]['uom_request']         = $rowProduct['uom_request'];

                        $promotional['promotion'][$indexP]['product_promo_id']    = $rowProduct['product_promo_id'];
                        $promotional['promotion'][$indexP]['qty_promo']           = $rowProduct['qty_promo'];
                        $promotional['promotion'][$indexP]['uom_promo']           = $rowProduct['uom_promo'];

                        $indexP++;
                    }
                    $product['Product']['promo']  = $promotional;
                } else {
                    $sqlPromoAll = mysql_query("SELECT promotional_details.promotional_id FROM promotional_details 
                    INNER JOIN promotionals ON promotionals.id = promotional_details.promotional_id 
                    WHERE promotionals.status = 2 AND promotionals.approved IS NOT NULL AND 
                    promotionals.approved_by IS NOT NULL AND promotionals.branch_id IS NULL AND promotionals.apply = 2 AND promotionals.start <= '".$dateOrder."' AND 
                    promotionals.end >= '".$dateOrder."' AND 
                    ((promotionals.customer_id = 1 OR promotionals.cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = 1)) OR (promotionals.customer_id IS NULL AND 
                    (promotionals.cgroup_id IS NULL OR promotionals.cgroup_id = ''))) AND 
                    promotional_details.product_request_id = ".$product['Product']['id']." 
                    ORDER BY promotionals.id DESC LIMIT 1;");
                    if(mysql_num_rows($sqlPromoAll)){
                        $indexP     = 0;
                        $rowPromo   = mysql_fetch_array($sqlPromoAll);
                        $promotional['promotion_id'] = $rowPromo[0];
                        $sqlProduct = mysql_query("SELECT product_request_id,qty_request,uom_request,product_promo_id,qty_promo,uom_promo, discount_percent, discount_amount FROM promotional_details WHERE promotional_id = ".$rowPromo[0]." AND product_request_id = ".$product['Product']['id']);
                        while($rowProduct = mysql_fetch_array($sqlProduct)){
                            $promotional['promotion'][$indexP]['uom_id'] = $rowProduct['uom_request'];
                            $promotional['promotion'][$indexP]['discount_percent'] = $rowProduct['discount_percent'];
                            $promotional['promotion'][$indexP]['discount_amount']  = $rowProduct['discount_amount'];

                            $promotional['promotion'][$indexP]['product_request_id']  = $rowProduct['product_request_id'];
                            $promotional['promotion'][$indexP]['qty_request']         = $rowProduct['qty_request'];
                            $promotional['promotion'][$indexP]['uom_request']         = $rowProduct['uom_request'];
    
                            $promotional['promotion'][$indexP]['product_promo_id']    = $rowProduct['product_promo_id'];
                            $promotional['promotion'][$indexP]['qty_promo']           = $rowProduct['qty_promo'];
                            $promotional['promotion'][$indexP]['uom_promo']           = $rowProduct['uom_promo'];

                            $indexP++;
                        }
                        $product['Product']['promo']  = $promotional;
                    } else {
                        $product['Product']['promo']  = '';
                    }
                }
            }
            echo json_encode($product);
            exit;
        }
    }

    function checkPromoType($branchId = null){
        $this->layout     = 'ajax';
        $promotype   = array();
        $dateOrder   = date("Y-m-d");
        $promoTypeId = '';
        if(!empty($branchId)){
            $sqlPosPromo = mysql_query("SELECT pt.id AS promotion_type_id FROM promotion_types pt 
            INNER JOIN promotype_branches pb ON pb.promotion_type_id=pt.id
            INNER JOIN pos_promotional_discounts ppd ON ppd.id=pb.pos_promotional_discount_id
            WHERE ppd.is_active=1 AND pt.is_active=1 AND ppd.branch_id='".$branchId."' ORDER BY promotion_type_id ASC ");
            if(mysql_num_rows($sqlPosPromo)){
                while($rowPosPromo = mysql_fetch_array($sqlPosPromo)){
                    $sqlPromo = mysql_query ("SELECT promotionals.promotion_type FROM promotional_details 
                    INNER JOIN promotionals ON promotionals.id = promotional_details.promotional_id 
                    WHERE promotionals.status = 2 AND promotionals.approved IS NOT NULL AND 
                    promotionals.approved_by IS NOT NULL AND
                    promotionals.branch_id = ".$branchId." AND
                    promotionals.promotion_type = ".$rowPosPromo['promotion_type_id']." AND
                    promotionals.apply = 1 AND promotionals.start <= '".$dateOrder."' AND 
                    promotionals.end >= '".$dateOrder."' AND
                    ((promotionals.customer_id = 1 OR promotionals.cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = 1)) OR (promotionals.customer_id IS NULL AND 
                    (promotionals.cgroup_id IS NULL OR promotionals.cgroup_id = ''))) 
                    ORDER BY promotionals.id DESC LIMIT 01;");
                    if(mysql_num_rows($sqlPromo)){
                        $promoTypeId .= $rowPosPromo['promotion_type_id'].',';
                    }
                }
            }
            $promotype['promotype'] = substr($promoTypeId,0,-1);
        }
        echo json_encode($promotype);
        exit;
    }

    function checkQtyPromotional($branchId = null,$productId = null,$promoTypeId = null){
        // Check Promotional
        $dateOrder   = date("Y-m-d");
        $promotional = array();
        if($promoTypeId != ''){
            $condition   = "";
            if($promoTypeId==1){
                $condition = " AND product_request_id = ".$productId;
            } //$sqlPromo  = mysql_query
            $sqlPromo = mysql_query ("SELECT promotional_details.promotional_id,promotional_details.product_request_id AS product_request_id FROM promotional_details 
            INNER JOIN promotionals ON promotionals.id = promotional_details.promotional_id 
            WHERE promotionals.status = 2 AND promotionals.approved IS NOT NULL AND 
            promotionals.approved_by IS NOT NULL AND
            promotionals.branch_id = ".$branchId." AND
            promotionals.promotion_type = ".$promoTypeId." AND
            promotionals.apply = 1 AND promotionals.start <= '".$dateOrder."' AND 
            promotionals.end >= '".$dateOrder."' AND
            ((promotionals.customer_id = 1 OR promotionals.cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = 1)) OR (promotionals.customer_id IS NULL AND 
            (promotionals.cgroup_id IS NULL OR promotionals.cgroup_id = ''))) ".$condition." 
            ORDER BY promotionals.id DESC LIMIT 01;");
            if(mysql_num_rows($sqlPromo)){
                $indexP    = 0;
                $rowPromo  = mysql_fetch_array($sqlPromo);
                $promotional['promotion_id'] = $rowPromo[0];
                $promotional['promotion_type_id'] = $promoTypeId;
                $sqlProduct = mysql_query("SELECT id,product_request_id,qty_request,uom_request,product_promo_id,qty_promo,uom_promo, discount_percent, discount_amount,unit_price FROM promotional_details WHERE promotional_id = ".$rowPromo[0].$condition );
                while($rowProduct    = mysql_fetch_array($sqlProduct)){
                    $sqlProductPromo = mysql_query("SELECT code FROM products WHERE is_active=1 AND id='".$rowProduct['product_promo_id']."' ");
                    $rowProductPromo = mysql_fetch_array($sqlProductPromo);
                    $promotional['promotion'][$indexP]['promo_detail_id']     = $rowProduct['id'];
                    $promotional['promotion'][$indexP]['product_request_id']  = $rowProduct['product_request_id'];
                    $promotional['promotion'][$indexP]['qty_request']         = $rowProduct['qty_request'];
                    $promotional['promotion'][$indexP]['uom_request']         = $rowProduct['uom_request'];

                    $promotional['promotion'][$indexP]['product_promo_id']    = $rowProduct['product_promo_id'];
                    $promotional['promotion'][$indexP]['product_code_promo']  = $rowProductPromo['code'];
                    $promotional['promotion'][$indexP]['qty_promo']           = $rowProduct['qty_promo'];
                    $promotional['promotion'][$indexP]['uom_promo']           = $rowProduct['uom_promo'];

                    $promotional['promotion'][$indexP]['discount_percent']    = $rowProduct['discount_percent'];
                    $promotional['promotion'][$indexP]['unit_price']          = $rowProduct['unit_price'];
                    $indexP++;
                }
            } else {
                $sqlPromoAll = mysql_query("SELECT promotional_details.promotional_id FROM promotional_details 
                INNER JOIN promotionals ON promotionals.id = promotional_details.promotional_id 
                WHERE promotionals.status = 2 AND 
                promotionals.promotion_type = ".$promoTypeId." AND
                promotionals.approved IS NOT NULL AND 
                promotionals.approved_by IS NOT NULL AND promotionals.branch_id IS NULL AND promotionals.apply = 2 AND promotionals.start <= '".$dateOrder."' AND 
                promotionals.end >= '".$dateOrder."' AND 
                ((promotionals.customer_id = 1 OR promotionals.cgroup_id IN (SELECT cgroup_id FROM customer_cgroups WHERE customer_id = 1)) OR (promotionals.customer_id IS NULL AND 
                (promotionals.cgroup_id IS NULL OR promotionals.cgroup_id = ''))) ".$condition." 
                ORDER BY promotionals.id DESC LIMIT 1;");
                if(mysql_num_rows($sqlPromoAll)){
                    $indexP     = 0;
                    $rowPromo   = mysql_fetch_array($sqlPromoAll);
                    $promotional['promotion_id'] = $rowPromo[0];
                    $promotional['promotion_type_id'] = $promoTypeId;
                    $sqlProduct = mysql_query("SELECT id,product_request_id,qty_request,uom_request,product_promo_id,qty_promo,uom_promo, discount_percent, discount_amount,unit_price FROM promotional_details WHERE promotional_id = ".$rowPromo[0].$condition);
                    while($rowProduct = mysql_fetch_array($sqlProduct)){
                        $promotional['promotion'][$indexP]['promo_detail_id']     = $rowProduct['id'];
                        $promotional['promotion'][$indexP]['product_request_id']  = $rowProduct['product_request_id'];
                        $promotional['promotion'][$indexP]['qty_request']         = $rowProduct['qty_request'];
                        $promotional['promotion'][$indexP]['uom_request']         = $rowProduct['uom_request'];

                        $promotional['promotion'][$indexP]['product_promo_id']    = $rowProduct['product_promo_id'];
                        $promotional['promotion'][$indexP]['qty_promo']           = $rowProduct['qty_promo'];
                        $promotional['promotion'][$indexP]['uom_promo']           = $rowProduct['uom_promo'];

                        $promotional['promotion'][$indexP]['discount_percent']    = $rowProduct['discount_percent'];
                        $promotional['promotion'][$indexP]['unit_price']          = $rowProduct['unit_price'];
                        $indexP++;
                    }
                }
            }
        }
        echo json_encode($promotional);
        exit;
    }
    
    function getProductPromoPoint(){
        $this->layout     = 'ajax';
        $promotionalPoint = array();
        $product   = array();
        $startDate = $_POST['start'];
        $endDate   = $_POST['end'];
        $branchId  = $_POST['branch_id'];
        if($startDate!="" && $endDate!=""){
            $condition='';
            if($branchId!=""){
                $condition = ' AND pp.branch_id = '.$branchId;
            }
            $sqlPromo = mysql_query("SELECT ppd.promotional_point_id AS promotional_point_id,ppd.product_request_id AS product_id,pp.total_point,IFNULL(pp.pgroup_id,'') AS pgroup_id,pp.point_in_dollar AS point_in_dollar
            FROM promotional_points AS pp
            INNER JOIN promotional_point_details AS ppd ON pp.id = ppd.promotional_point_id
            WHERE pp.status = 2 AND pp.approved IS NOT NULL AND pp.approved_by IS NOT NULL AND pp.apply = 1 AND pp.start = '".$startDate."' AND pp.end = '".$endDate."' {$condition} ORDER BY pp.id DESC LIMIT 1;");
            if(mysql_num_rows($sqlPromo)){
                $indexP   = 0;
                $rowPromo = mysql_fetch_array($sqlPromo);
                $promotionalPoint['promotion_id']    = $rowPromo['promotional_point_id'];
                $promotionalPoint['total_point']     = $rowPromo['total_point'];
                $promotionalPoint['point_in_dollar'] = $rowPromo['point_in_dollar'];
                if(!empty($rowPromo['pgroup_id'])){
                    $sqlProduct = mysql_query("SELECT ppd.uom_request, ppd.discount_percent, ppd.discount_amount,ppd.product_request_id AS product_id
                    FROM promotional_point_details AS ppd
                    INNER JOIN product_pgroups AS ppg ON ppg.product_id = ppd.product_request_id
                    INNER JOIN pgroups AS pg ON pg.id = ppg.pgroup_id
                    WHERE pg.id='".$rowPromo['pgroup_id']."' AND ppd.promotional_point_id = ".$rowPromo['promotional_point_id']);
                }else{
                    $sqlProduct = mysql_query("SELECT uom_request, discount_percent, discount_amount,product_request_id AS product_id FROM promotional_point_details WHERE promotional_point_id = ".$rowPromo['promotional_point_id']);
                }
                while($rowProduct = mysql_fetch_array($sqlProduct)){
                    $promotionalPoint['product'][$indexP]['product_id']       = $rowProduct['product_id'];
                    $promotionalPoint['product'][$indexP]['uom_id']           = $rowProduct['uom_request'];
                    $promotionalPoint['product'][$indexP]['discount_percent'] = $rowProduct['discount_percent'];
                    $promotionalPoint['product'][$indexP]['discount_amount']  = $rowProduct['discount_amount'];
                    $indexP++;
                }
            }else{
                $sqlPromoAll = mysql_query("SELECT pp.id AS promotional_point_id,pp.total_point AS total_point,IFNULL(pp.pgroup_id,'') AS pgroup_id,pp.point_in_dollar AS point_in_dollar FROM promotional_points AS pp WHERE pp.status = 2 AND pp.approved IS NOT NULL AND pp.approved_by IS NOT NULL AND pp.apply = 1 AND pp.start = '".$startDate."' AND pp.end = '".$endDate."' ORDER BY pp.id DESC LIMIT 1;");
                if(mysql_num_rows($sqlPromoAll)){
                    $rowPromo = mysql_fetch_array($sqlPromoAll);
                    if(!empty($rowPromo['pgroup_id'])){
                        $sqlProduct = mysql_query("SELECT pp.id AS promotional_point_id,pp.total_point AS total_point,pp.point_in_dollar AS point_in_dollar FROM promotional_points AS pp INNER JOIN product_pgroups AS ppg ON ppg.pgroup_id = pp.pgroup_id WHERE pg.id='".$rowPromo['pgroup_id']."' AND ppd.promotional_point_id = ".$rowPromo['promotional_point_id']);
                    }else{
                        $sqlProduct = mysql_query("SELECT pp.id AS promotional_point_id,pp.total_point AS total_point,pp.point_in_dollar AS point_in_dollar FROM promotional_points AS pp WHERE pp.id = ".$rowPromo['promotional_point_id']);
                    }
                    $rowProduct = mysql_fetch_array($sqlProduct);
                    $promotionalPoint['promotion_id']    = $rowProduct['promotional_point_id'];
                    $promotionalPoint['total_point']     = $rowProduct['total_point'];
                    $promotionalPoint['point_in_dollar'] = $rowProduct['point_in_dollar'];
                    $promotionalPoint['product'][$indexP]['product_id']       = '';
                    $promotionalPoint['product'][$indexP]['uom_id']           = '';
                    $promotionalPoint['product'][$indexP]['discount_percent'] = 0;
                    $promotionalPoint['product'][$indexP]['discount_amount']  = 0;
                }
            }
        }
        echo json_encode($promotionalPoint);
        exit;
    }

    function membershipCardDis($customerId=null){
        $this->layout = 'ajax';      
        $this->set(compact('customerId'));
    }

    function checkMembershipCard($customerId=null){
        $this->layout = 'ajax';
        $today  = date('Y-m-d');
        $result = array();
        if(trim($_POST['account'])!="" && $customerId != ''){
            $queryCheckCard = mysql_query("SELECT membership_cards.*, customers.name_kh AS customer_name_kh,customers.name AS customer_name, customers.main_number, customers.dob, customers.id AS customer_id FROM membership_cards 
            INNER JOIN customers ON customers.id = membership_cards.customer_id
            WHERE membership_cards.is_active = 1 AND customer_id='".$customerId."' AND (card_id = '".trim($_POST['account'])."' OR customers.main_number = '".trim($_POST['account'])."') LIMIT 1");
            if(mysql_num_rows($queryCheckCard)){
                while ($rowCheckCard = mysql_fetch_array($queryCheckCard)) {
                    $dateEnd = $rowCheckCard['card_date_end'];
                    if ($today <= $dateEnd) {
                        $extraDis = 0;
                        if(date('m-d') == date('m-d', strtotime($rowCheckCard['dob']))){
                            $extraDis = 5;
                        }
                        $result['card_id']         = $rowCheckCard['id'];
                        $result['discount']        = $rowCheckCard['discount_percent']+$extraDis;
                        $result['discount_extra']  = $extraDis;
                        $result['number']          = $rowCheckCard['card_id'];
                        $result['name']            = $rowCheckCard['customer_name_kh'].'-('.$rowCheckCard['customer_name'].')';
                        $result['telephone']       = $rowCheckCard['main_number'];
                        $result['other_telephone'] = "";
                        $result['info']            = 'You are successfully!';
                        $result['status']          = 1;
                        $result['customer_id']     = $rowCheckCard['customer_id'];

                        $result['card_date_start'] = $rowCheckCard['card_date_start'];
                        $result['card_date_end']   = $rowCheckCard['card_date_end'];
                        
                        $result['point_in_pollar'] = $rowCheckCard['point_in_dollar'];
                        $result['exchange_point']  = $rowCheckCard['exchange_point'];
                        $result['total_point']     = $rowCheckCard['total_point'];
                    }else{
                        $result['card_id']  = "";
                        $result['discount'] = 0;
                        $result['discount_extra'] = 0;
                        $result['card_id'] = "";
                        $result['number']  = "";
                        $result['name'] = "";
                        $result['telephone'] = "";
                        $result['other_telephone'] = "";
                        $result['info'] = 'Your card has been expired!';
                        $result['status'] = 0;
                        $result['customer_id'] = '';
                        $result['point_in_pollar'] =  '';
                        $result['exchange_point'] =  '';
                        $result['total_point'] =  '';

                        $result['card_date_start'] = '';
                        $result['card_date_end']   = '';
                    }
                }
            } else{
                $result['card_id'] = "";
                $result['discount'] = 0;
                $result['discount_extra'] = 0;
                $result['info'] = 'Invalid card!';
                $result['status'] = 0;
            }
        }else{
            $result['card_id'] = "";
            $result['discount'] = 0;
            $result['discount_extra'] = 0;
            $result['info'] = 'Fail!';
            $result['status'] = 0;  
        }
        echo json_encode($result);
        exit;
    }
    
    function getTotalQtyByLotExp($productId = null, $locationGroupId = null, $lotNumber = null, $expiry = null){
        $this->layout = 'ajax';
        $totalQty = array();
        if(empty($productId) || empty($locationGroupId)){
            $totalQty['total'] = 0;
            echo json_encode($totalQty);
            exit;
        }
        if(empty($lotNumber)){
            $lotNumber = 0;
        }
        if(empty($expiry)){
            $expiry = '0000-00-00';
        }
        // Check Warehouse Option Allow Negative
        $allowStockMinus = false;
        $warehouseOption = ClassRegistry::init('LocationGroup')->findById($locationGroupId);
        if($warehouseOption['LocationGroup']['allow_negative_stock'] == 1){
            $allowStockMinus = true;
        }
        // Check Allow Negative Stock
        if($allowStockMinus == true){ // Allow Negative Stock
            $totalQty['total'] = 1000000;
        } else {
            $sqlStock = mysql_query("SELECT IFNULL(SUM(total_qty - total_order), 0) AS total FROM ".$locationGroupId."_group_totals WHERE product_id = ".$productId." AND lots_number = '".$lotNumber."' AND expired_date = '".$expiry."' AND location_id IN (SELECT id FROM locations WHERE location_group_id = ".$locationGroupId." AND is_active = 1)");
            if(mysql_num_rows($sqlStock)){
                $rowStock = mysql_fetch_array($sqlStock);
                $totalQty['total'] = $rowStock[0];
            } else {
                $totalQty['total'] = $rowStock[0];
            }
        }
        echo json_encode($totalQty);
        exit;
    }

    function changeDiscount() {
        $this->layout = 'ajax';
    }

    function productDiscount() {
        $this->layout = 'ajax';
    }

    function printReceipt($salesInvoiceId = null) {
        $this->layout = 'ajax';
        if (!empty($salesInvoiceId)) {
            $salesInvoiceReceipt = ClassRegistry::init('SalesInvoiceReceipt')->find("first", array('conditions' => array('SalesInvoiceReceipt.sales_order_id' => $salesInvoiceId, 'SalesInvoiceReceipt.is_void' => 0)));

            $salesInvoice = ClassRegistry::init('SalesInvoice')->find("first", array('conditions' => array('SalesInvoice.id' => $salesInvoiceId)));
            $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array(
                "conditions" => array("ExchangeRate.is_active" => 1),
                "order" => array("ExchangeRate.created desc")
                    )
            );
            $salesInvoiceDetails = ClassRegistry::init('SalesInvoiceDetail')->find("all", array('conditions' => array('SalesInvoiceDetail.sales_order_id' => $salesInvoiceId)));
            $salesInvoiceServices = ClassRegistry::init('SalesInvoiceService')->find("all", array('conditions' => array('SalesInvoiceService.sales_order_id' => $salesInvoice['SalesInvoice']['id'])));
            // Currency Other
            $otherSymbolCur = '';
            if($salesInvoiceReceipt['SalesInvoiceReceipt']['exchange_rate_id'] != ''){
                $sqlOtherCur   = mysql_query("SELECT currencies.symbol FROM exchange_rates INNER JOIN currencies ON currencies.id = exchange_rates.currency_id WHERE exchange_rates.id = ".$salesInvoiceReceipt['SalesInvoiceReceipt']['exchange_rate_id']." LIMIT 1");
                if(mysql_num_rows($sqlOtherCur)){
                    $rowOtherCur    = mysql_fetch_array($sqlOtherCur);
                    $otherSymbolCur = $rowOtherCur[0];
                }
            }
            $this->set(compact('otherSymbolCur', 'SalesInvoice', 'SalesInvoiceDetails', 'SalesInvoiceReceipt', 'lastExchangeRate', 'SalesInvoiceServices'));
        } else {
            exit;
        }
    }

    function void($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->loadModel('SalesInvoice');
        $this->loadModel('GeneralLedger');
        $this->loadModel('InventoryValuation');
        $this->loadModel('Transaction');
        $salesInvoice = ClassRegistry::init('SalesInvoice')->find("first", array('conditions' => array('SalesInvoice.id' => $id)));
        if($salesInvoice['SalesInvoice']['status'] == 2){
            // Check Save Transaction
            $checkTransaction = true;
            $transactionLogId = 0;
            $dateNow  = date("Y-m-d H:i:s");
            $sqlCheck = mysql_query("SELECT * FROM transactions WHERE type = 'Sales POS' AND action = 1 AND module_id = ".$id);
            if(mysql_num_rows($sqlCheck)){
                $rowCheck  = mysql_fetch_array($sqlCheck);
                $sqlDetail = mysql_query("SELECT * FROM transaction_details WHERE transaction_id = ".$rowCheck['id']);
                $rowDetail = mysql_num_rows($sqlDetail);
                $transactionLogId = $rowCheck['id'];
                if($rowDetail > 0){
                    // Check Total Transaction
                    $totalD = $rowCheck['products'] + $rowCheck['service'];
                    if($totalD != $rowDetail){
                        $checkTransaction = false;
                    } else {
                        $totalAcctD = 0;
                        while($rowD = mysql_fetch_array($sqlDetail)){
                            $totalAcctD += $rowD['save_acct'];
                            if($rowD['type'] == 1){ 
                                if($rowD['inventory_valutaion'] != '1'){
                                    $checkTransaction = false;
                                    break;
                                }
                                if($salesInvoice['SalesInvoice']['status'] == 2){
                                    if($rowD['inventory'] != '1' || $rowD['inventory_total'] != '1' || $rowD['loc_inventory'] != '1' || $rowD['loc_inventory_total'] != '1' || $rowD['loc_inventory_detail'] != '1' || $rowD['g_inventory'] != '1' || $rowD['g_inventory_detail'] != '1'){
                                        $checkTransaction = false;
                                        break;
                                    }
                                }
                            }
                        }
                        if($checkTransaction == true){
                            // Check Account
                            $sqlAcct = mysql_query("SELECT COUNT(id) FROM general_ledger_details WHERE general_ledger_id = (SELECT id FROM general_ledgers WHERE sales_order_id = ".$id." AND sales_order_receipt_id IS NULL LIMIT 1)");
                            if(mysql_num_rows($sqlAcct)){
                                $rowAcct = mysql_fetch_array($sqlAcct);
                                if($rowAcct[0] != ($totalAcctD + $rowCheck['save_acct'])){
                                    $checkTransaction = false;
                                }
                            } else {
                                $checkTransaction = false;
                            }
                        }
                    }
                } else {
                    $checkTransaction = false;
                }
            }
            // if($checkTransaction == false){
            //     $this->Helper->saveUserActivity($user['User']['id'], 'Point Of Sales', 'Void (Error Save Transaction)', $id);
            //     echo MESSAGE_CLOUD_NOT_EDIT_TRANSACTION;
            //     exit;
            // }
            // Remove Transaction Log
            if($transactionLogId > 0){
                mysql_query("DELETE FROM transactions WHERE id = ".$transactionLogId);
                mysql_query("DELETE FROM transaction_details WHERE transaction_id = ".$transactionLogId);
            }
            $salesInvoiceDetails = ClassRegistry::init('SalesInvoiceDetail')->find("all", array('conditions' => array('SalesInvoiceDetail.sales_order_id' => $id)));
            $posPickDetails    = ClassRegistry::init('PosPickDetail')->find("all", array('conditions' => array('PosPickDetail.sales_order_id' => $id)));
            $dateSale = $salesInvoice['SalesInvoice']['order_date'];
            // Update Product In Location Group
            foreach ($salesInvoiceDetails as $salesInvoiceDetail) {
                $totalQtyOrder = (($salesInvoiceDetail['SalesInvoiceDetail']['qty'] + $salesInvoiceDetail['SalesInvoiceDetail']['qty_free']) * $salesInvoiceDetail['SalesInvoiceDetail']['conversion']);
                $qtyOrder      = ($salesInvoiceDetail['SalesInvoiceDetail']['qty'] * $salesInvoiceDetail['SalesInvoiceDetail']['conversion']);
                $qtyFree       = ($salesInvoiceDetail['SalesInvoiceDetail']['qty_free'] * $salesInvoiceDetail['SalesInvoiceDetail']['conversion']);
                // Update Inventory
                $dataGroup = array();
                $dataGroup['module_type']       = 9;
                $dataGroup['point_of_sales_id'] = $id;
                $dataGroup['product_id']        = $salesInvoiceDetail['SalesInvoiceDetail']['product_id'];
                $dataGroup['location_group_id'] = $salesInvoice['SalesInvoice']['location_group_id'];
                $dataGroup['date']         = $dateSale;
                $dataGroup['total_qty']    = $totalQtyOrder;
                $dataGroup['total_order']  = $qtyOrder;
                $dataGroup['total_free']   = $qtyFree;
                $dataGroup['transaction_id'] = '';
                // Update Inventory Group
                $this->Inventory->saveGroupTotalDetail($dataGroup);

            }
            // Update Product By Lot & Exp Date
            foreach($posPickDetails AS $posPickDetail){
                $totalOrder = $posPickDetail['PosPickDetail']['total_qty'];
                // Update Inventory (Void POS)
                $data = array();
                $data['module_type']       = 9;
                $data['point_of_sales_id'] = $id;
                $data['product_id']        = $posPickDetail['PosPickDetail']['product_id'];
                $data['location_id']       = $posPickDetail['PosPickDetail']['location_id'];
                $data['location_group_id'] = $salesInvoice['SalesInvoice']['location_group_id'];
                $data['lots_number']  = $posPickDetail['PosPickDetail']['lots_number'];
                $data['expired_date'] = $posPickDetail['PosPickDetail']['expired_date'];
                $data['date']         = $dateSale;
                $data['total_qty']    = $totalOrder;
                $data['total_order']  = $totalOrder;
                $data['total_free']   = 0;
                $data['user_id']      = $user['User']['id'];
                $data['customer_id']  = $salesInvoice['SalesInvoice']['customer_id'];
                $data['vendor_id']    = "";
                $data['unit_price']   = 0;
                $data['unit_cost']    = 0;
                $data['transaction_id'] = '';
                // Update Invetory Location
                $this->Inventory->saveInventory($data);
            }

            $this->SalesInvoice->updateAll(
                    array('SalesInvoice.status' => 0, 'SalesInvoice.modified_by' => $user['User']['id']), array('SalesInvoice.id' => $id)
            );
            $this->InventoryValuation->updateAll(
                    array('InventoryValuation.is_active' => "2"), array('InventoryValuation.point_of_sales_id' => $salesInvoice['SalesInvoice']['id'])
            );
            $this->GeneralLedger->updateAll(
                    array('GeneralLedger.is_active' => 2), array('GeneralLedger.sales_order_id' => $id)
            );
            $this->Transaction->create();
            $transaction = array();
            $transaction['Transaction']['module_id']  = $id;
            $transaction['Transaction']['type']       = 'Sales POS';
            $transaction['Transaction']['action']     = 2;
            $transaction['Transaction']['created']    = $dateNow;
            $transaction['Transaction']['created_by'] = $user['User']['id'];
            $this->Transaction->save($transaction);
            $this->Helper->saveUserActivity($user['User']['id'], 'Point Of Sales', 'Void', $id);
            echo MESSAGE_DATA_HAS_BEEN_DELETED;
            exit;
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Point Of Sales (Error)', 'Void', $id);
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }

    function add($tab = '0'){
        $this->layout = 'pos';
        $user  = $this->getCurrentUser();
        // Run Config Path
        $this->Helper->checkConfigPath('Test Path');
        if (!empty($this->data)) {
            $this->layout = 'ajax';
            $this->loadModel('SalesInvoiceReceipt');
            $this->loadModel('GeneralLedger');
            $this->loadModel('GeneralLedgerDetail');
            $this->loadModel('Company');
            $this->loadModel('Branch');
            $this->loadModel('AccountType');
            $this->loadModel('Transaction');
            $this->loadModel('TransactionDetail');
            //  Find Chart Account
            $cashBankAccount  = $this->AccountType->findById(6);
            $arAccount        = $this->AccountType->findById(7);
            $salesDiscAccount = $this->AccountType->findById(11);
            $result          = array();
            $checkError      = 1;
            $listOutStock    = "";
            $totalPriceSales = 0;
            $jsonArray  = array();
            $branch     = $this->Branch->read(null, $this->data['PointOfSale']['branch_id']);
            $company    = $this->Company->read(null, $this->data['PointOfSale']['company_id']);
            $classId    = $this->Helper->getClassId($company['Company']['id'], $company['Company']['classes'], $this->data['PointOfSale']['location_group_id']);
            $productOrder = array();
            for ($i = 0; $i < sizeof($this->data['SalesInvoiceDetail']['product_id']); $i++) {
                if ($this->data['SalesInvoiceDetail']['product_id'][$i] != '') {
                    $expDate  = $this->data['SalesInvoiceDetail']['expired_date'][$i]!=''?$this->data['SalesInvoiceDetail']['expired_date'][$i]:'0000-00-00';
                    $keyIndex = $this->data['SalesInvoiceDetail']['product_id'][$i]."+".$expDate;
                    if (array_key_exists($keyIndex, $productOrder)){
                        $productOrder[$keyIndex]['qty'] += ($this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['qty'][$i]) + $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['qty_free'][$i])) * $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['conversion'][$i]);
                    } else {
                        $productOrder[$keyIndex]['qty'] = ($this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['qty'][$i]) + $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['qty_free'][$i])) * $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['conversion'][$i]);
                    }
                }
                // Make Sales Detail As Array
                $totalPriceSales                  += $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['total_price'][$i]) -  $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['discount_amount'][$i]);
                $jsonArray[$i]['discount_id']      = $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['discount_id'][$i]);
                $jsonArray[$i]['discount_amount']  = $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['discount_amount'][$i]);
                $jsonArray[$i]['discount_percent'] = $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['discount_percent'][$i]);
                $jsonArray[$i]['product_id']       = $this->data['SalesInvoiceDetail']['product_id'][$i];
                $jsonArray[$i]['service_id']       = $this->data['SalesInvoiceDetail']['service_id'][$i];
                $jsonArray[$i]['qty_uom_id']       = $this->data['SalesInvoiceDetail']['qty_uom_id'][$i];
                $jsonArray[$i]['qty']              = $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['qty'][$i]);
                $jsonArray[$i]['qty_free']         = $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['qty_free'][$i]);
                $jsonArray[$i]['unit_price']       = $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['unit_price'][$i]);
                $jsonArray[$i]['total_price']      = $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['total_price'][$i]);
                $jsonArray[$i]['qty_order']        = ($this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['qty'][$i]) + $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['qty_free'][$i])) * $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['conversion'][$i]);
                $jsonArray[$i]['conversion']       = $this->Helper->replaceThousand($this->data['SalesInvoiceDetail']['conversion'][$i]);
                $jsonArray[$i]['lots_number']      = $this->data['SalesInvoiceDetail']['lots_number'][$i]!=''?$this->data['SalesInvoiceDetail']['lots_number'][$i]:0;
                $jsonArray[$i]['expired_date']     = $this->data['SalesInvoiceDetail']['expired_date'][$i]!=''?$this->data['SalesInvoiceDetail']['expired_date'][$i]:'0000-00-00';
                $jsonArray[$i]['promotional_id']   = $this->data['SalesInvoiceDetail']['promotional_id'][$i];
                $jsonArray[$i]['class_id']         = $classId;
            }
            // Check Warehouse Option Allow Negative
            $allowStockMinus = false;
            $warehouseOption = ClassRegistry::init('LocationGroup')->findById($this->data['PointOfSale']['location_group_id']);
            if($warehouseOption['LocationGroup']['allow_negative_stock'] == 1){
                $allowStockMinus = true;
            }
            
            if($allowStockMinus == false){ // Not Allow Negative Stock Check Stock On Hand
                // Check Qty in Stock Before Save
                foreach($productOrder AS $key => $order){
                    $check = explode("+", $key);
                    $productId = $check[0];
                    $expDate   = $check[1];
                    if($check[1] == ''){
                        $expDate = '0000-00-00';
                    }
                    $sqlStock = mysql_query("SELECT SUM(total_qty - total_order) FROM `".$this->data['PointOfSale']['location_group_id'] . "_group_totals` WHERE product_id = ".$productId." AND expired_date = '".$expDate."' AND location_id IN (SELECT id FROM locations WHERE location_group_id = ".$this->data['PointOfSale']['location_group_id'].") GROUP BY product_id");
                    if (mysql_num_rows($sqlStock)) {
                        $stock = mysql_fetch_array($sqlStock);
                        $totalQtyStock = $this->Helper->replaceThousand($stock[0]); // Total Qty Stock
                        if ($totalQtyStock < $order['qty']) {
                            $checkError = 2;
                            $listOutStock .= $productId."-";
                        }
                    } else {
                        $checkError = 2;
                        $listOutStock .= $productId."-";
                    }
                }
            }
            // Check Error Qty in Stock
            if ($checkError == 1) {
                if (@$this->data['PointOfSale']['location_group_id'] != "" && @$this->data['PointOfSales']['total_amount'] != "" && @$this->data['PointOfSale']['balance_us'] != "") {
                    $dateNow  = date("Y-m-d H:i:s");
                    // Update Code & Change SO Generate Code
                    $modComCode = ClassRegistry::init('ModuleCodeBranch')->find('first', array('conditions' => array("ModuleCodeBranch.branch_id" => $this->data['PointOfSale']['branch_id'])));
                    $posCode    = date("y").$modComCode['ModuleCodeBranch']['pos_code'];
                    $salesInvoice = array();
                    if($this->data['PointOfSale']['order_date'] == ""){
                        $this->data['PointOfSale']['order_date'] = $this->Helper->checkDateTransaction($this->data['PointOfSale']['branch_id']);
                    }
                    $this->SalesInvoice->create();
                    $salesInvoice['SalesInvoice']['sys_code']    = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                    $salesInvoice['SalesInvoice']['customer_id'] = $this->data['PointOfSale']['customer_id']!=''?$this->data['PointOfSale']['customer_id']:1;
                    $salesInvoice['SalesInvoice']['company_id']  = $this->data['PointOfSale']['company_id'];
                    $salesInvoice['SalesInvoice']['branch_id']   = $this->data['PointOfSale']['branch_id'];
                    $salesInvoice['SalesInvoice']['location_group_id']    = $this->data['PointOfSale']['location_group_id'];
                    $salesInvoice['SalesInvoice']['currency_id']   = $company['Company']['currency_id'];
                    $salesInvoice['SalesInvoice']['vat_chart_account_id'] = $this->data['PointOfSale']['vat_chart_account_id'];
                    $salesInvoice['SalesInvoice']['ar_id']           = $this->data['PointOfSale']['chart_account_id'];
                    $salesInvoice['SalesInvoice']['so_code']         = $posCode;
                    $salesInvoice['SalesInvoice']['total_amount']    = $totalPriceSales;
                    $salesInvoice['SalesInvoice']['balance']         = $this->Helper->replaceThousand($this->data['PointOfSale']['balance_us']);
                    $salesInvoice['SalesInvoice']['discount']        = $this->Helper->replaceThousand($this->data['PointOfSale']['discount']);
                    $salesInvoice['SalesInvoice']['discount_percent']     = $this->Helper->replaceThousand($this->data['PointOfSale']['discount_percent']);
                    $salesInvoice['SalesInvoice']['bank_charge_amount']   = $this->Helper->replaceThousand($this->data['PointOfSale']['bank_charge_amount']);
                    $salesInvoice['SalesInvoice']['bank_charge_percent']  = $this->Helper->replaceThousand($this->data['PointOfSale']['bank_charge_percent']);
                    $salesInvoice['SalesInvoice']['pos_pay_method_id']    = $this->data['PointOfSale']['pos_pay_method_id'];
                    $salesInvoice['SalesInvoice']['order_date']      = $this->data['PointOfSale']['order_date'];
                    $salesInvoice['SalesInvoice']['total_vat']       = $this->data['PointOfSale']['total_vat'];
                    $salesInvoice['SalesInvoice']['vat_percent']     = $this->data['PointOfSale']['vat_percent'];
                    $salesInvoice['SalesInvoice']['vat_setting_id']  = $this->data['PointOfSale']['vat_setting_id'];
                    $salesInvoice['SalesInvoice']['vat_calculate']   = $this->data['PointOfSale']['vat_calculate'];
                    $salesInvoice['SalesInvoice']['price_type_id']   = $this->data['PointOfSale']['price_type_id'];
                    $salesInvoice['SalesInvoice']['sales_rep_id']    = 0;
                    $salesInvoice['SalesInvoice']['shift_id']        = $this->data['PointOfSale']['shift_id'];

                    $salesInvoice['SalesInvoice']['card_code']           = $this->data['PointOfSale']['card_code'];
                    $salesInvoice['SalesInvoice']['membership_card_id']  = $this->data['PointOfSale']['membership_card_id'];
                    $salesInvoice['SalesInvoice']['total_point']         = $this->data['PointOfSale']['total_point'];
                    $salesInvoice['SalesInvoice']['remain_point']        = $this->data['PointOfSale']['remain_point'];
                    $salesInvoice['SalesInvoice']['promotional_type']    = $this->data['PointOfSale']['promotional_type'];
                    $salesInvoice['SalesInvoice']['promotion_point_id']  = $this->data['PointOfSale']['promotion_point_id'];
                    
                    if(!empty($this->data['PointOfSale']['memo'])){
                        $salesInvoice['SalesInvoice']['memo']        = $this->data['PointOfSale']['memo'];
                    }
                    $salesInvoice['SalesInvoice']['is_pos']          = 1;
                    $salesInvoice['SalesInvoice']['status']          = 2;
                    $salesInvoice['SalesInvoice']['created']         = $dateNow;
                    $salesInvoice['SalesInvoice']['created_by']      = $user['User']['id'];
                    if ($this->SalesInvoice->save($salesInvoice)) {
                        $salesInvoiceId = $this->SalesInvoice->id;
                        // Get Module Code
                        $modCode    = $this->Helper->getModuleCode($posCode, $salesInvoiceId, 'so_code', 'sales_invoices', 'status >= 0 AND branch_id = '.$this->data['PointOfSale']['branch_id']);
                        // Updaet Module Code
                        $invPOSCode = $modCode;
                        $totalPoint = 0;
                        mysql_query("UPDATE sales_invoices SET so_code = '".$modCode."' WHERE id = ".$salesInvoiceId);//promotional_type
                        if(!empty($this->data['PointOfSale']['membership_card_id'])){
                            if($this->data['PointOfSale']['promotional_type']==1){
                                $sqlMemCard = mysql_query("SELECT total_point FROM membership_cards WHERE is_active=1 AND id='".$this->data['PointOfSale']['membership_card_id']."' ");
                                if(mysql_num_rows($sqlMemCard)){
                                    $rowMemCard = mysql_fetch_array($sqlMemCard);
                                    $totalPoint = ($this->data['PointOfSale']['total_point']+$rowMemCard['total_point']);
                                }
                            }else{
                                $totalPoint = $this->data['PointOfSale']['remain_point'];
                            }
                          
                            mysql_query("UPDATE `membership_cards` SET `total_point`='". ($totalPoint)."' WHERE `id`= ".$this->data['PointOfSale']['membership_card_id']);
                        }
                        // Transaction 
                        $transactionAcct = 0;
                        $transactionPro  = 0;
                        $transactionSer  = 0;
                        $transaction = array();
                        $this->Transaction->create();
                        $transaction['Transaction']['module_id']  = $salesInvoiceId;
                        $transaction['Transaction']['type']       = 'Sales POS';
                        $transaction['Transaction']['created']    = $dateNow;
                        $transaction['Transaction']['created_by'] = $user['User']['id'];
                        $this->Transaction->save($transaction);
                        $transactionId = $this->Transaction->id;
                        // Create General Ledger
                        $this->GeneralLedger->create();
                        $generalLedger = array();
                        $generalLedger['GeneralLedger']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                        $generalLedger['GeneralLedger']['sales_order_id'] = $salesInvoiceId;
                        $generalLedger['GeneralLedger']['date']       = $this->data['PointOfSale']['order_date'];
                        $generalLedger['GeneralLedger']['reference']  = $modCode;
                        $generalLedger['GeneralLedger']['created_by'] = $user['User']['id'];
                        $generalLedger['GeneralLedger']['is_sys'] = 1;
                        $generalLedger['GeneralLedger']['is_adj'] = 0;
                        $generalLedger['GeneralLedger']['is_active'] = 1;
                        $this->GeneralLedger->save($generalLedger);
                        $glId = $this->GeneralLedger->id;
                        $chartAccountPOS = $cashBankAccount['AccountType']['chart_account_id'];
                        if($salesInvoice['SalesInvoice']['balance'] > 0){
                            $chartAccountPOS = $arAccount['AccountType']['chart_account_id'];
                        }
                        // General Ledger Detail Cash
                        $this->GeneralLedgerDetail->create();
                        $generalLedgerDetail = array();
                        $generalLedgerDetail['GeneralLedgerDetail']['general_ledger_id'] = $glId;
                        $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id']  = $chartAccountPOS;
                        $generalLedgerDetail['GeneralLedgerDetail']['company_id']  = $this->data['PointOfSale']['company_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['branch_id']   = $this->data['PointOfSale']['branch_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['location_group_id'] = $this->data['PointOfSale']['location_group_id'];
                        $generalLedgerDetail['GeneralLedgerDetail']['customer_id'] = $this->data['PointOfSale']['customer_id']!=''?$this->data['PointOfSale']['customer_id']:1;
                        $generalLedgerDetail['GeneralLedgerDetail']['type']   = 'POS';
                        $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $salesInvoice['SalesInvoice']['total_amount'] - $this->Helper->replaceThousand($this->data['PointOfSale']['discount']) + $this->data['PointOfSale']['total_vat'];
                        $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                        $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: POS # ' . $modCode;
                        $generalLedgerDetail['GeneralLedgerDetail']['class_id'] = $classId;
                        $this->GeneralLedgerDetail->save($generalLedgerDetail);
                        $transactionAcct++;
                        // General Ledger Detail (General Discount)
                        if ($this->Helper->replaceThousand($this->data['PointOfSale']['discount']) > 0) {
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesDiscAccount['AccountType']['chart_account_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $this->Helper->replaceThousand($this->data['PointOfSale']['discount']);
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: POS # ' . $modCode . ' Total Discount';
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $transactionAcct++;
                        }
                        // General Ledger Detail Total VAT
                        if ($this->Helper->replaceThousand($salesInvoice['SalesInvoice']['total_vat']) > 0) {
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = $salesInvoice['SalesInvoice']['vat_chart_account_id'];
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = $salesInvoice['SalesInvoice']['total_vat'];
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: POS # ' . $modCode . ' Total VAT';
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $transactionAcct++;
                        }
                        // General Ledger Detail (Bank Charge)
                        if ($this->Helper->replaceThousand($this->data['PointOfSale']['bank_charge_amount']) > 0) {
                            $this->GeneralLedgerDetail->create();
                            $generalLedgerDetail['GeneralLedgerDetail']['chart_account_id'] = 97;
                            $generalLedgerDetail['GeneralLedgerDetail']['debit']  = $this->Helper->replaceThousand($this->data['PointOfSale']['bank_charge_amount']);
                            $generalLedgerDetail['GeneralLedgerDetail']['credit'] = 0;
                            $generalLedgerDetail['GeneralLedgerDetail']['memo']   = 'ICS: POS # ' . $modCode . ' Bank Charge';
                            $this->GeneralLedgerDetail->save($generalLedgerDetail);
                            $transactionAcct++;
                        }
                        // Send File to Process Second
                        $rand = rand();
                        $name = (isset($_SESSION['sPosDb']) ? $_SESSION['sPosDb'] : $rand) . "_pos_" . $salesInvoiceId;
                        $filename = "public/pos/" . $name;
                        shell_exec("touch " . $filename);
                        if (file_exists($filename)) {
                            $json = json_encode($jsonArray);
                            $file = fopen($filename, "w");
                            fwrite($file, $json);
                            fclose($file);
                            $url = LINK_URL . "deliveryPos?user=" . $user['User']['id'] . "&sales_order_id=" . $salesInvoiceId . "&location_group_id=" . $this->data['PointOfSale']['location_group_id'] . "&total_amount_us=" . $salesInvoice['SalesInvoice']['total_amount'] . "&gl=" . $glId . "&company_id= ".$this->data['PointOfSale']['company_id']."&calculate_cogs=".$this->data['PointOfSale']['calculate_cogs']." &json=" . $name;
                            $url = "wget -b -q -P public/pos/logs/ '" . $url . "' " . LINK_URL_SSL;
                            shell_exec($url);
                            // Save Sales Receipt
                            if($salesInvoice['SalesInvoice']['balance'] == 0){
                                $this->SalesInvoiceReceipt->create();
                                $salesInvoiceReceipt = array();
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['sys_code']           = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['sales_order_id']     = $salesInvoiceId;
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['branch_id']          = $this->data['PointOfSale']['branch_id'];
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['receipt_code']       = '';
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['pay_date']           = $this->data['PointOfSale']['order_date'];
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['currency_id'] = $this->data['PointOfSale']['currency_id'];
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['exchange_rate_id']   = $this->data['PointOfSale']['exchange_rate_id'];
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['amount_us']          = $this->Helper->replaceThousand($this->data['PointOfSale']['paid_us']);
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['amount_other']       = $this->Helper->replaceThousand($this->data['PointOfSale']['paid_kh']);
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['total_amount']       = $this->Helper->replaceThousand($this->data['PointOfSale']['total_be_paid']);
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['total_amount_other'] = $this->Helper->replaceThousand($this->data['PointOfSale']['total_be_paid_kh']);
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['balance']      = 0;
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['change']       = $this->Helper->replaceThousand($this->data['PointOfSale']['change_us']);
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['change_other'] = $this->Helper->replaceThousand($this->data['PointOfSale']['change_kh']);
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['created']      = $dateNow;
                                $salesInvoiceReceipt['SalesInvoiceReceipt']['created_by']   = $user['User']['id'];
                                $this->SalesInvoiceReceipt->save($salesInvoiceReceipt);
                                $saleReceiptId = $this->SalesInvoiceReceipt->id;
                                // Get Module Code
                                $posRepCode = date("y").$modComCode['ModuleCodeBranch']['pos_rep_code'];
                                $modCode    = $this->Helper->getModuleCode($posRepCode, $saleReceiptId, 'receipt_code', 'sales_invoice_receipts', 'is_void = 0 AND branch_id = '.$this->data['PointOfSale']['branch_id']);
                                // Updaet Module Code
                                mysql_query("UPDATE sales_invoice_receipts SET receipt_code = '".$modCode."' WHERE id = ".$saleReceiptId);
                            }
                            // Update Transaction Save
                            mysql_query("UPDATE transactions SET save_acct = ".$transactionAcct.", products=".$transactionPro.", service=".$transactionSer." WHERE id = ".$transactionId);
                            $this->Helper->saveUserActivity($user['User']['id'], 'Point Of Sales', 'Save Add New', $salesInvoiceId);
                            // Assign Value to Layout Print
                            $result['error']      = 0;
                            $result['inv_code']   = $invPOSCode;
                            $result['inv_date']   = $this->Helper->dateShort($this->data['PointOfSale']['order_date'], "d/m/Y").' '.date("H:i:s");
                            $result['print_date'] = date("d/m/Y H:i:s");
                            $result['com_photo']  = $company['Company']['photo'];
                            $result['branch_add'] = nl2br($branch['Branch']['address']);
                            $result['username']   = $user['User']['first_name']." ".$user['User']['last_name'];
                            echo json_encode($result);
                            exit;
                        } else {
                            $this->data['SalesInvoice']['id'] = $salesInvoiceId;
                            $this->data['SalesInvoice']['is_pos'] = 1;
                            $this->data['SalesInvoice']['status'] = -1;
                            $this->SalesInvoice->save($this->data);
                            $result['error'] = 1;
                            echo json_encode($result);
                            exit;
                        }
                    } else {
                        $result['error'] = 2;
                        echo json_encode($result);
                        exit;
                    }
                } else {
                    $result['error'] = 3;
                    echo json_encode($result);
                    exit;
                }
            } else {
                $result['error'] = 4;
                $result['stock'] = $listOutStock;
                echo json_encode($result);
                exit;
            }
        }

        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Point Of Sales', 'Add New');
            $companies = ClassRegistry::init('Company')->find('all',
                            array(
                                'joins' => array(
                                    array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                                ),
                                'fields' => array('Company.id', 'Company.name', 'Company.vat_calculate', 'Company.currency_id'),
                                'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])
                            ));
            $branches = ClassRegistry::init('Branch')->find('all',
                            array(
                                'joins' => array(
                                    array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')),
                                    array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))
                                ),
                                'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.inv_code', 'Branch.currency_id', 'Branch.pos_currency_id', 'Branch.telephone', 'Currency.symbol', 'Branch.acc_num', 'Branch.acc_holder','Branch.bank'),
                                'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])
                            ));
            $arAccount   = ClassRegistry::init('AccountType')->findById(7);
            $arAccountId = $arAccount['AccountType']['chart_account_id'];
            if($user['User']['id'] == 1){
                $conditionUser = "";
            }else{
                $conditionUser = "id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            }
            $joinUsers = array('table' => 'user_location_groups', 'type' => 'INNER', 'conditions' => array('user_location_groups.location_group_id=LocationGroup.id'));
            $joinLocation = array('table' => 'locations', 'type' => 'INNER', 'conditions' => array('locations.location_group_id=LocationGroup.id'));
            $locationGroups = ClassRegistry::init('LocationGroup')->find('list', array('fields' => array('LocationGroup.id', 'LocationGroup.name'),'joins' => array($joinUsers, $joinLocation),'conditions' => array('user_location_groups.user_id=' . $user['User']['id'], 'LocationGroup.is_active' => '1', 'LocationGroup.location_group_type_id != 1'), 'group' => 'LocationGroup.id'));
            $cgroups = ClassRegistry::init('Cgroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, $conditionUser)));
            $pgroups = ClassRegistry::init('Pgroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT pgroup_id FROM pgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].'))')));
            $uoms = ClassRegistry::init('Uom')->find("list", array("conditions" => array("Uom.is_active = 1")));
            $this->set(compact('companies', "branches", 'locationGroups', "arAccountId", "cgroups", "pgroups", "uoms", "tab"));
        }
    }
    
    function reprintReceiptSm($code = null, $branchId = null) {
        $this->layout = 'ajax';
        if (!empty($code)) {
            $salesInvoice = ClassRegistry::init('SalesInvoice')->find("first", array('conditions' => array('SalesInvoice.so_code' => $code, 'SalesInvoice.branch_id' => $branchId, 'SalesInvoice.status' => 2, 'SalesInvoice.is_pos' => 1)));
            if(!empty($salesInvoice)){
                $salesInvoiceId = $salesInvoice['SalesInvoice']['id'];
                $salesInvoiceReceipt = ClassRegistry::init('SalesInvoiceReceipt')->find("first", array('conditions' => array('SalesInvoiceReceipt.sales_order_id' => $salesInvoiceId, 'SalesInvoiceReceipt.is_void' => 0)));
                $lastExchangeRate = ClassRegistry::init('ExchangeRate')->find("first", array(
                    "conditions" => array("ExchangeRate.is_active" => 1),
                    "order" => array("ExchangeRate.created desc")
                        )
                );
                $salesInvoiceDetails = ClassRegistry::init('SalesInvoiceDetail')->find("all", array('conditions' => array('SalesInvoiceDetail.sales_order_id' => $salesInvoiceId)));
                $salesInvoiceServices = ClassRegistry::init('SalesInvoiceService')->find("all", array('conditions' => array('SalesInvoiceService.sales_order_id' => $salesInvoice['SalesInvoice']['id'])));
                // Currency Other
                $otherSymbolCur = '';
                @$sqlOtherCur   = mysql_query("SELECT currencies.symbol FROM exchange_rates INNER JOIN currencies ON currencies.id = exchange_rates.currency_id WHERE exchange_rates.id = ".$salesInvoiceReceipt['SalesInvoiceReceipt']['exchange_rate_id']." LIMIT 1");
                if(mysql_num_rows($sqlOtherCur)){
                    $rowOtherCur    = mysql_fetch_array($sqlOtherCur);
                    $otherSymbolCur = $rowOtherCur[0];
                }
                $this->set(compact('otherSymbolCur', 'SalesInvoice', 'SalesInvoiceDetails', 'SalesInvoiceReceipt', 'lastExchangeRate', 'SalesInvoiceServices'));
            } else {
                echo "error";
                exit;
            }
        } else {
            echo "error";
            exit;
        }
    }

    function service($companyId, $branchId) {
        $this->layout = 'ajax';
        $serviceP = array();
        $sqlSG = mysql_query("SELECT section_id FROM services WHERE is_active = 1 GROUP BY section_id;");
        while($rowSG = mysql_fetch_array($sqlSG)){
            $serviceP[] = $rowSG['section_id'];
        }
        $conSection = '';
        if(!empty($serviceP)){
            $conSection = ' AND Pgroup.id IN ('.implode(",",$serviceP).')';
        }
        $sections = ClassRegistry::init('Pgroup')->find("list", array("conditions" => array("Pgroup.is_active = 1".$conSection)));
        $services = $this->serviceCombo($companyId, $branchId);
        $this->set(compact('sections', 'services'));
    }

    function serviceCombo($companyId, $branchId) {
        $array = array();
        $services = ClassRegistry::init('Service')->find("all", array("conditions" => array("Service.company_id=" . $companyId. " AND Service.is_active = 1", "Service.id IN (SELECT service_id FROM service_branches WHERE branch_id = ".$branchId.")")));
        foreach ($services as $service) {
            $queryUomName = mysql_query("SELECT name FROM uoms WHERE id = '".$service['Service']['uom_id']."'");
            $dataUomName  = mysql_fetch_array($queryUomName);            
            array_push($array, array('value' => $service['Service']['id'], 'name' => $service['Service']['name'], 'section' => $service['Pgroup']['id'], 'code' => $service['Service']['code'], 'uom-name' => $dataUomName[0], 'uom-id' => $service['Service']['uom_id'], 'price' => $service['Service']['unit_price']));
        }
        return $array;
    }
    
    function viewPosDaily(){
        $this->layout = 'ajax';
    }
    
    function customer($companyId) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $this->set('companyId', $companyId);
        }else{
            exit;
        }
    }

    function customerAjax($companyId, $group = null) {
        $this->layout = 'ajax';
        if(!empty($companyId)){
            $this->set('companyId', $companyId);
            $this->set('group', $group);
        }else{
            exit;
        }
    }
    
    function discount($companyId = null) {
        $this->layout = 'ajax';
        if (!$companyId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $discounts = ClassRegistry::init('Discount')->find("all", array('conditions' => array('Discount.is_active' => 1, 'Discount.company_id' => $companyId), 'order' => array('id DESC')));
        $this->set(compact('discounts'));
    }
    
    function checkStartShift($companyId = null, $branchId = null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!$companyId && !$branchId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $queryStarShift = mysql_query("SELECT id, shift_code, created, total_register, total_register_other, status FROM shifts WHERE created_by = '".$user['User']['id']."' AND company_id = '".$companyId."' AND branch_id = '".$branchId."' ORDER BY id DESC LIMIT 01");
        if(mysql_num_rows($queryStarShift)){
            $dataStarShift = mysql_fetch_array($queryStarShift);
            if($dataStarShift[5] == 3){
                $result['status_shift'] = 0;
                $result['not_collect']  = 0;
                echo json_encode($result);
                exit;
            } else if($dataStarShift[5] == 2){
                $result['status_shift'] = 0;
                $result['not_collect']  = 1;
                echo json_encode($result);
                exit;
            } else {
                $totalAdj      = 0;
                $totalAdjOther = 0;
                $queryAdj = mysql_query("SELECT SUM(total_adj), SUM(total_adj_other) FROM shift_adjusts WHERE shift_id = '".$dataStarShift[0]."' GROUP BY shift_id");
                if(mysql_num_rows($queryAdj)){
                    $dataAdj = mysql_fetch_array($queryAdj);
                    $totalAdj      = $dataAdj[0];
                    $totalAdjOther = $dataAdj[1];
                }
                
                $result['status_shift'] = $dataStarShift[0];
                $result['shift_code'] = $dataStarShift[1];
                $result['shift_created'] = date('d/m/Y H:i:s', strtotime($dataStarShift[2]));
                $result['total_register'] = $dataStarShift[3];
                $result['total_register_other'] = $dataStarShift[4];
                $result['total_adj'] = $totalAdj;
                $result['total_adj_other'] = number_format($totalAdjOther, 0);
                echo json_encode($result);
                exit;
            }
        }else{
            //Don't Have Data
            $result['status_shift'] = 0;
            echo json_encode($result);
            exit;
        }
    }
    
    function addShiftRegister($companyId = null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!$companyId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }        
        
        if(!empty($this->data)){
            $dateNow  = date("Y-m-d H:i:s");
            $this->loadModel("Shift");
            $this->Shift->create();            
            $code  = $this->Helper->getAutoGenerateShiftCode();
            $this->data['Shift']['shift_code'] = $code;
            $this->data['Shift']['date_start'] = $dateNow;
            $this->data['Shift']['date_end']   = $dateNow;
            $this->data['Shift']['created']    = $dateNow;
            $this->data['Shift']['created_by'] = $user['User']['id'];
            $this->data['Shift']['status']     = 1;
            if ($this->Shift->save($this->data)) {    
                $lastInsertId = $this->Shift->getLastInsertId();
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Shift', 'Save Add New', $lastInsertId);
                echo $lastInsertId."|*|".$code."|*|".date("d/m/Y H:i:s", strtotime($dateNow));
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Shift', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
    }    
    
    function saveAdjShiftRegister($shiftId = null, $type){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!$shiftId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }        
        
        if(!empty($this->data)){
            $dateNow  = date("Y-m-d H:i:s");
            $this->loadModel("ShiftAdjust");
            $this->ShiftAdjust->create();
            
            if($type == 2){
                $totalAdj      = $this->Helper->replaceThousand($this->data['ShiftAdjust']['total_adj']) * -1;
                $totalAdjOther = $this->Helper->replaceThousand($this->data['ShiftAdjust']['total_adj_other']) * -1;
            }else{
                $totalAdj      = $this->Helper->replaceThousand($this->data['ShiftAdjust']['total_adj']);
                $totalAdjOther = $this->Helper->replaceThousand($this->data['ShiftAdjust']['total_adj_other']);
            }
            
            $this->data['ShiftAdjust']['total_adj']         = $totalAdj;
            $this->data['ShiftAdjust']['total_adj_other']   = $totalAdjOther;
            $this->data['ShiftAdjust']['created']           = $dateNow;
            $this->data['ShiftAdjust']['created_by']        = $user['User']['id'];
            if ($this->ShiftAdjust->save($this->data)) {    
                $lastInsertId = $this->ShiftAdjust->getLastInsertId();
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Adjust Shift', 'Save Add New', $lastInsertId);
                // Sum Adj Shift
                $totalAdj      = 0;
                $totalAdjOther = 0;
                $queryAdj = mysql_query("SELECT SUM(total_adj), SUM(total_adj_other) FROM shift_adjusts WHERE shift_id = '".$shiftId."' GROUP BY shift_id");
                if(mysql_num_rows($queryAdj)){
                    $dataAdj = mysql_fetch_array($queryAdj);
                    $totalAdj      = $dataAdj[0];
                    $totalAdjOther = $dataAdj[1];
                }
                
                echo $lastInsertId."|*|".$totalAdj."|*|".$totalAdjOther;
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Adjust Shift', 'Save Add New (Error)');
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
    }   
    
    function endShiftRegister($companyId = null, $shiftId = null){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!$companyId && !$shiftId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        if(!empty($this->data)){ 
            $this->loadModel("Shift");
            $dateNow    = date("Y-m-d H:i:s");
            $totalSales = 0;
            $querySales = mysql_query("SELECT SUM(total_amount + total_vat - discount) totalSales FROM sales_invoices WHERE shift_id = ".$shiftId." AND status = 2");
            if(mysql_num_rows($querySales)){
                $dataSales  = mysql_fetch_array($querySales);
                $totalSales = $dataSales[0];
            }
            $this->data['Shift']['id']     = $shiftId;
            $this->data['Shift']['status'] = 2;
            $this->data['Shift']['total_acture']       = $this->data['Shift']['total_acture'];
            $this->data['Shift']['total_acture_other'] = $this->data['Shift']['total_acture_other'];
            $this->data['Shift']['total_sales']        = $totalSales;
            $this->data['Shift']['close_shift_memo']   = $this->data['Shift']['close_shift_memo'];
            $this->data['Shift']['date_end']           = $dateNow;
            if($this->Shift->save($this->data)){
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Shift', 'Save End Shift', $shiftId);
                echo MESSAGE_DATA_HAS_BEEN_SAVED;
                exit;
            } else {
                // Save User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Shift', 'Save End Shift (Error)', $shiftId);
                echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                exit;
            }
        }
    }
    
    function checkAdjShiftRegister($shiftId = null){
        $this->layout = 'ajax';       
        if (!$shiftId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }  
        $adjShift      = "0.00";
        $adjShiftOther = "0.00";
        $queryAdjShift = mysql_query("SELECT SUM(`total_adj`), SUM(`total_adj_other`) FROM `shift_adjusts` WHERE `shift_id` = '".$shiftId."' GROUP BY `shift_id`");
        if(mysql_num_rows($queryAdjShift)){
            $dataAdjShift  = mysql_fetch_array($queryAdjShift);
            $adjShift      = $dataAdjShift[0];
            $adjShiftOther = $dataAdjShift[1];
        }
        $result['adjShift'] = $adjShift;
        $result['adjShiftOther'] = $adjShiftOther;
        echo json_encode($result);
        exit;
    }
    
    function getDataAdjShiftRegister($shiftId = null){ 
        $this->layout = 'ajax';       
        $user = $this->getCurrentUser();
        if (!$shiftId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        } 
        $this->loadModel("Shift");
        $shifts = $this->Shift->read(null, $shiftId);
        if (!empty($shifts)){  
            $branch = ClassRegistry::init('Branch')->find('first',
                            array(
                                'joins' => array(
                                    array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')),
                                    array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))
                                ),
                                'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.inv_code', 'Branch.currency_id', 'Branch.pos_currency_id', 'Currency.symbol', 'Branch.address', 'Branch.telephone'),
                                'conditions' => array('Branch.is_active = 1', 'Branch.id = '.$shifts['Shift']['branch_id'].'', 'user_branches.user_id=' . $user['User']['id'])
                            ));
            
            $this->set(compact('shiftId', 'branch'));         
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Shift', 'Print Shift (Error)');
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
    }
    function printShift($shiftId = null) {
        $this->layout = 'ajax';       
        if (!$shiftId) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }        
        $user = $this->getCurrentUser();
        $this->loadModel("Shift");
        $shifts = $this->Shift->read(null, $shiftId);
        if (!empty($shifts)){    
            $company = ClassRegistry::init('Company')->find('first',
                            array(
                                'joins' => array(
                                    array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))
                                ),
                                'fields' => array('Company.photo'),
                                'conditions' => array('Company.is_active = 1', 'Company.id = '.$shifts['Shift']['company_id'].'', 'user_companies.user_id=' . $user['User']['id'])
                            ));
            $branch = ClassRegistry::init('Branch')->find('first',
                            array(
                                'joins' => array(
                                    array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id')),
                                    array('table' => 'module_code_branches AS ModuleCodeBranch', 'type' => 'left', 'conditions' => array('ModuleCodeBranch.branch_id=Branch.id'))
                                ),
                                'fields' => array('Branch.id', 'Branch.name', 'Branch.company_id', 'ModuleCodeBranch.inv_code', 'Branch.currency_id', 'Branch.pos_currency_id', 'Currency.symbol', 'Branch.address', 'Branch.telephone'),
                                'conditions' => array('Branch.is_active = 1', 'Branch.id = '.$shifts['Shift']['branch_id'].'', 'user_branches.user_id=' . $user['User']['id'])
                            ));
            
            $totalAdj      = 0;
            $totalAdjOther = 0;
            $queryAdj = mysql_query("SELECT SUM(total_adj), SUM(total_adj_other) FROM shift_adjusts WHERE shift_id = '".$shiftId."' GROUP BY shift_id");
            if(mysql_num_rows($queryAdj)){
                $dataAdj = mysql_fetch_array($queryAdj);
                $totalAdj      = $dataAdj[0];
                $totalAdjOther = $dataAdj[1];
            }
            
            $this->set(compact('shifts', 'branch', 'company', 'totalAdj', 'totalAdjOther'));            
        } else {
            $this->Helper->saveUserActivity($user['User']['id'], 'Shift', 'Print Shift (Error)');
            echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
            exit;
        }
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
                $r = 0;
                $e = 0;
                $syncEco   = array();
                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->Pgroup->create();
                $this->data['Pgroup']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Pgroup']['created']    = $dateNow;
                $this->data['Pgroup']['created_by'] = $user['User']['id'];
                $this->data['Pgroup']['is_active']  = 1;
                if ($this->Pgroup->save($this->data)) {
                    $pgroupId = $this->Pgroup->id;
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Pgroup'], 'pgroups');
                    $restCode[$r]['modified']   = $dateNow;
                    $restCode[$r]['dbtodo']     = 'pgroups';
                    $restCode[$r]['actodo']     = 'is';
                    $r++;
                    // Pgroup Company
                    if (!empty($this->data['Pgroup']['company_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Pgroup']['company_id']); $i++) {
                            mysql_query("INSERT INTO pgroup_companies (pgroup_id, company_id) VALUES ('" . $pgroupId . "','" . $this->data['Pgroup']['company_id'][$i] . "')");
                            // Convert to REST
                            $restCode[$r]['pgroup_id']  = $this->data['Pgroup']['sys_code'];
                            $restCode[$r]['company_id'] = $this->Helper->getSQLSysCode("companies", $this->data['Pgroup']['company_id'][$i]);
                            $restCode[$r]['dbtodo']     = 'pgroup_companies';
                            $restCode[$r]['actodo']     = 'is';
                            $r++;
                        }
                    }
                    // Send to E-Commerce
                    // Convert to REST
                    $syncEco[$e]['sys_code']  = $this->data['Pgroup']['sys_code'];
                    $syncEco[$e]['name']      = $this->data['Pgroup']['name'];
                    $syncEco[$e]['status']    = 2;
                    $syncEco[$e]['created']   = $dateNow;
                    $syncEco[$e]['dbtodo']    = 'pgroups';
                    $syncEco[$e]['actodo']    = 'is';
                    $e++;
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save File Send to E-Commerce
                    $this->Helper->sendFileToSyncPublic($syncEco);
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
                $r = 0;
                $restCode = array();
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
                        $restCode[$r] = $this->Helper->convertToDataSync($this->data['Uom'], 'uoms');
                        $restCode[$r]['modified'] = $dateNow;
                        $restCode[$r]['dbtodo']   = 'uoms';
                        $restCode[$r]['actodo']   = 'is';
                        $r++;
                        // Send to E-Commerce
                        $e = 0;
                        $syncEco = array();
                        // Convert to REST
                        $syncEco[$e]['sys_code']  = $this->data['Uom']['sys_code'];
                        $syncEco[$e]['name']      = $this->data['Uom']['name'];
                        $syncEco[$e]['abbr']      = $this->data['Uom']['abbr'];
                        $syncEco[$e]['created']   = $dateNow;
                        $syncEco[$e]['dbtodo']    = 'uoms';
                        $syncEco[$e]['actodo']    = 'is';
                        // Save File Send to E-Commerce
                        $this->Helper->sendFileToSyncPublic($syncEco);
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
                                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['UomConversion'], 'uom_conversions');
                                    $restCode[$r]['modified']   = $dateNow;
                                    $restCode[$r]['dbtodo']     = 'uom_conversions';
                                    $restCode[$r]['actodo']     = 'is';
                                    $r++;
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
                                                $restCode[$r] = $this->Helper->convertToDataSync($otherUom['UomConversion'], 'uom_conversions');
                                                $restCode[$r]['modified']   = $dateNow;
                                                $restCode[$r]['dbtodo']     = 'uom_conversions';
                                                $restCode[$r]['actodo']     = 'is';
                                                $r++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        // Save File Send
                        $this->Helper->sendFileToSync($restCode, 0, 0);
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
    
    function quickAddProduct() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Product');
            $this->Product->create();
            if ($this->Helper->checkDouplicate('code', 'products', $this->data['Product']['code'], "company_id=".$this->data['Product']['company_id']." AND is_active = 1")) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Save Quick Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $e = 0;
                $syncEco  = array();
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $smValUom = ClassRegistry::init('UomConversion')->find('first', array('fileds' => array('value'), 'order' => 'id', 'conditions' => array('from_uom_id' => $this->data['Product']['price_uom_id'], 'is_small_uom = 1', 'is_active' => 1)));
                if (!empty($smValUom)) {
                    $this->data['Product']['small_val_uom'] = $smValUom['UomConversion']['value'];
                } else {
                    $this->data['Product']['small_val_uom'] = 1;
                }
                if($this->data['Product']['code'] == ""){
                    $this->data['Product']['code'] = $this->data['Product']['barcode'];
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
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Product'], 'products');
                    $restCode[$r]['modified'] = $dateNow;
                    $restCode[$r]['dbtodo']   = 'products';
                    $restCode[$r]['actodo']   = 'is';
                    $r++;
                    // Check Product Group Share
                    $checkShare = 2;
                    if (!empty($this->data['Product']['pgroup_id'])) {
                        $sqlShare = mysql_query("SELECT id FROM e_pgroup_shares WHERE pgroup_id = ".$this->data['Product']['pgroup_id']);
                        if(mysql_num_rows($sqlShare)){
                            $checkShare = 1;
                        }
                    }
                    // Send to E-Commerce
                    // Convert to REST
                    $shopSys = $this->Helper->getSQLSysCode("companies", $this->data['Product']['company_id']);
                    $syncEco[$e]['shop_id']   = $shopSys;
                    $syncEco[$e]['uom_id']    = $this->Helper->getSQLSysCode("uoms", $this->data['Product']['price_uom_id']);
                    $syncEco[$e]['sys_code']  = $this->data['Product']['sys_code'];
                    $syncEco[$e]['code']      = $this->data['Product']['code'];
                    $syncEco[$e]['barcode']   = $this->data['Product']['barcode'];
                    $syncEco[$e]['name']      = $this->data['Product']['name'];
                    $syncEco[$e]['description'] = $this->data['Product']['description'];
                    $syncEco[$e]['status']    = $checkShare;
                    $syncEco[$e]['created']   = $dateNow;
                    $syncEco[$e]['dbtodo']    = 'products';
                    $syncEco[$e]['actodo']    = 'is';
                    $e++;
                    if($checkShare == 1){
                        mysql_query("INSERT INTO `e_product_shares` (`company_id`, `product_id`, `created`, `created_by`) VALUES (".$this->data['Product']['company_id'].", ".$lastInsertId.", '".$dateNow."', ".$user['User']['id'].");");
                    }
                    // product group
                    if (!empty($this->data['Product']['pgroup_id'])) {
                        mysql_query("INSERT INTO product_pgroups (product_id, pgroup_id) VALUES ('".$lastInsertId."', '".$this->data['Product']['pgroup_id']."')");
                        // Convert to REST
                        $restCode[$r]['product_id'] = $this->data['Product']['sys_code'];
                        $restCode[$r]['pgroup_id']  = $this->Helper->getSQLSysCode("pgroups", $this->data['Product']['pgroup_id']);
                        $restCode[$r]['dbtodo']     = 'product_pgroups';
                        $restCode[$r]['actodo']     = 'is';
                        $r++;
                        // Convert to REST
                        $syncEco[$e]['product_id'] = $this->data['Product']['sys_code'];
                        $syncEco[$e]['pgroup_id']  = $this->Helper->getSQLSysCode("pgroups", $this->data['Product']['pgroup_id']);
                        $syncEco[$e]['dbtodo']     = 'product_pgroups';
                        $syncEco[$e]['actodo']     = 'is';
                        $e++;
                    }
                    // SKU of each UOM
                    if (!empty($this->data['sku_uom_value'])) {
                        for ($i = 0; $i < sizeof($this->data['sku_uom_value']); $i++) {
                            if ($this->data['sku_uom_value'][$i] != '' && $this->data['sku_uom'][$i] != '') {
                                mysql_query("INSERT INTO product_with_skus (product_id, sku, uom_id) VALUES ('" . $lastInsertId . "', '" . $this->data['sku_uom_value'][$i] . "', '" . $this->data['sku_uom'][$i] . "')");
                                // Convert to REST
                                $restCode[$r]['product_id'] = $this->data['Product']['sys_code'];
                                $restCode[$r]['sku']        = $this->data['sku_uom_value'][$i];
                                $restCode[$r]['uom_id']     = $this->Helper->getSQLSysCode("uoms", $this->data['sku_uom'][$i]);
                                $restCode[$r]['dbtodo']     = 'product_with_skus';
                                $restCode[$r]['actodo']     = 'is';
                                $r++;
                            }
                        }
                    }
                    if (!empty($this->data['Product']['branch_id'])) {
                        for ($i = 0; $i < sizeof($this->data['Product']['branch_id']); $i++) {
                            mysql_query("INSERT INTO product_branches (product_id,branch_id) VALUES ('" . $lastInsertId . "','" . $this->data['Product']['branch_id'][$i] . "')");
                            // Convert to REST
                            $restCode[$r]['product_id'] = $this->data['Product']['sys_code'];
                            $restCode[$r]['branch_id']  = $this->Helper->getSQLSysCode("branches", $this->data['Product']['branch_id'][$i]);
                            $restCode[$r]['dbtodo']     = 'product_branches';
                            $restCode[$r]['actodo']     = 'is';
                            $r++;
                        }
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save File Send to E-Commerce
                    $this->Helper->sendFileToSyncPublic($syncEco);
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
        }
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Product', 'Quick Add New');
        $companies = ClassRegistry::init('Company')->find('list', array('joins' => array(array('table' => 'user_companies', 'type' => 'inner', 'conditions' => array('user_companies.company_id=Company.id'))), 'conditions' => array('Company.is_active = 1', 'user_companies.user_id=' . $user['User']['id'])));
        $branches  = ClassRegistry::init('Branch')->find('list', array('joins' => array(array('table' => 'user_branches', 'type' => 'inner', 'conditions' => array('user_branches.branch_id=Branch.id'))), 'conditions' => array('Branch.is_active = 1', 'user_branches.user_id=' . $user['User']['id'])));
        $pgroups   = ClassRegistry::init('Pgroup')->find('list', array('order' => 'Pgroup.name', 'conditions' => array('Pgroup.is_active' => 1, 'Pgroup.id IN (SELECT pgroup_id FROM pgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].'))')));
        $uoms      = ClassRegistry::init('Uom')->find("list", array("conditions" => array("Uom.is_active = 1"), "order" => "Uom.name"));
        $this->set(compact("companies", "branches", "uoms", "pgroups"));
    }
    
    function getSkuUom($uomId = null) {
        $this->layout = 'ajax';
        if ($uomId != null) {
            $this->set('uomId', $uomId);
        } else {
            echo "Error Select Uom";
        }
    }
    
    function addCgroup(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Cgroup');
            $result = array();
            $comCheck = $this->data['Cgroup']['company_id'];
            if ($this->Helper->checkDouplicate('name', 'cgroups', $this->data['Cgroup']['name'], 'is_active = 1 AND id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN ('.$comCheck.'))')) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Save Quick Add New (Name ready existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
                $r = 0;
                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->Cgroup->create();
                $user = $this->getCurrentUser();
                $this->data['Cgroup']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Cgroup']['created']    = $dateNow;
                $this->data['Cgroup']['created_by'] = $user['User']['id'];
                $this->data['Cgroup']['is_active']  = 1;
                if ($this->Cgroup->save($this->data)) {
                    $lastInsertId = $this->Cgroup->getLastInsertId();
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Cgroup'], 'cgroups');
                    $restCode[$r]['modified'] = $dateNow;
                    $restCode[$r]['dbtodo']   = 'cgroups';
                    $restCode[$r]['actodo']   = 'is';
                    $r++;
                    // Cgroup company
                    if (!empty($this->data['Cgroup']['company_id'])) {
                        mysql_query("INSERT INTO cgroup_companies (cgroup_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Cgroup']['company_id'] . "')");
                        // Convert to REST
                        $restCode[$r]['cgroup_id']   = $this->data['Cgroup']['sys_code'];
                        $restCode[$r]['company_id']  = $this->Helper->getSQLSysCode("companies", $this->data['Cgroup']['company_id']);
                        $restCode[$r]['modified']    = $dateNow;
                        $restCode[$r]['dbtodo']      = 'cgroup_companies';
                        $restCode[$r]['actodo']      = 'is';
                        $r++;
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Save Quick Add New', $lastInsertId);
                    $result['error']  = 0;
                    $result['option'] = '<option value="">'.INPUT_SELECT.'</option>';
                    $cgroups = ClassRegistry::init('Cgroup')->find('all', array('order' => 'name', 'conditions' => array('is_active' => 1)));
                    foreach($cgroups AS $cgroup){
                        $selected = '';
                        if($cgroup['Cgroup']['id'] == $lastInsertId){
                            $selected = 'selected="selected"';
                        }
                        $result['option'] .= '<option value="'.$cgroup['Cgroup']['id'].'" '.$selected.'>'.$cgroup['Cgroup']['name'].'</option>';
                    }
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Customer Group', 'Quick Add New');
        $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
        $this->set(compact("companies"));
    }
    
    function addTerm(){
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('PaymentTerm');
            $result = array();
            if ($this->Helper->checkDouplicate('name', 'payment_terms', $this->data['PaymentTerm']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Save Quick Add New (Name ready existed)');
                $result['error'] = 2;
                echo json_encode($result);
                exit;
            } else {
                $r = 0;
                $restCode = array();
                $dateNow  = date("Y-m-d H:i:s");
                $this->PaymentTerm->create();
                $this->data['PaymentTerm']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['PaymentTerm']['created']    = $dateNow;
                $this->data['PaymentTerm']['created_by'] = $user['User']['id'];
                $this->data['PaymentTerm']['is_active'] = 1;
                if ($this->PaymentTerm->save($this->data)) {
                    $termId = $this->PaymentTerm->id;
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['PaymentTerm'], 'payment_terms');
                    $restCode[$r]['modified']   = $dateNow;
                    $restCode[$r]['dbtodo']     = 'payment_terms';
                    $restCode[$r]['actodo']     = 'is';
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Save Quick Add New', $termId);
                    $result['error']  = 0;
                    $result['option'] = '<option value="">'.INPUT_SELECT.'</option>';
                    $terms = ClassRegistry::init('PaymentTerm')->find('all', array('order' => 'name', 'conditions' => array('is_active' => 1)));
                    foreach($terms AS $term){
                        $selected = '';
                        if($term['PaymentTerm']['id'] == $termId){
                            $selected = 'selected="selected"';
                        }
                        $result['option'] .= '<option value="'.$term['PaymentTerm']['id'].'" '.$selected.'>'.$term['PaymentTerm']['name'].'</option>';
                    }
                    echo json_encode($result);
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Save Quick Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Payment Term', 'Quick Add New');
    }
    
    function quickAddCustomer(){
        $this->layout = "ajax";
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            $this->loadModel('Customer');
            if ($this->Helper->checkDouplicate('name', 'customers', $this->data['Customer']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Vendor', 'Save Quick Add New (Name ready existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $r = 0;
                $restCode  = array();
                $dateNow   = date("Y-m-d H:i:s");
                $this->Customer->create();
                $this->data['Customer']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['Customer']['type']       = 2;
                $this->data['Customer']['created']    = $dateNow;
                $this->data['Customer']['created_by'] = $user['User']['id'];
                $this->data['Customer']['is_active']  = 1;
                if ($this->Customer->save($this->data)) {
                    $lastInsertId = $this->Customer->getLastInsertId();
                    // Convert to REST
                    $restCode[$r] = $this->Helper->convertToDataSync($this->data['Customer'], 'customers');
                    $restCode[$r]['modified']   = $dateNow;
                    $restCode[$r]['dbtodo']     = 'customers';
                    $restCode[$r]['actodo']     = 'is';
                    $r++;
                    // Customer group
                    if (!empty($this->data['Customer']['cgroup_id'])) {
                        mysql_query("INSERT INTO customer_cgroups (customer_id,cgroup_id) VALUES ('" . $lastInsertId . "','" . $this->data['Customer']['cgroup_id'] . "')");
                        // Convert to REST
                        $restCode[$r]['customer_id'] = $this->data['Customer']['sys_code'];
                        $restCode[$r]['cgroup_id']   = $this->Helper->getSQLSysCode("cgroups", $this->data['Customer']['cgroup_id']);
                        $restCode[$r]['dbtodo']      = 'customer_cgroups';
                        $restCode[$r]['actodo']      =  'is';
                        $r++;
                    }
                    // Customer Company
                    if (isset($this->data['Customer']['company_id'])) {
                        mysql_query("INSERT INTO customer_companies (customer_id, company_id) VALUES ('" . $lastInsertId . "','" . $this->data['Customer']['company_id'] . "')");
                        // Convert to REST
                        $restCode[$r]['customer_id'] = $this->data['Customer']['sys_code'];
                        $restCode[$r]['company_id']  = $this->Helper->getSQLSysCode("companies", $this->data['Customer']['company_id']);
                        $restCode[$r]['dbtodo']      = 'customer_companies';
                        $restCode[$r]['actodo']      = 'is';
                        $r++;
                    }
                    // Save File Send
                    $this->Helper->sendFileToSync($restCode, 0, 0);
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Quick Add New', $lastInsertId);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Save Quick Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if(empty($this->data)){
            $this->Helper->saveUserActivity($user['User']['id'], 'Customer', 'Quick Add New');
            $conditionUser = "id IN (SELECT cgroup_id FROM cgroup_companies WHERE company_id IN (SELECT company_id FROM user_companies WHERE user_id = ".$user['User']['id']."))";
            $companies = ClassRegistry::init('Company')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, 'id IN (SELECT company_id FROM user_companies WHERE user_id = '.$user['User']['id'].')')));
            $cgroups   = ClassRegistry::init('Cgroup')->find('list', array('order' => 'id', 'conditions' => array('is_active' => 1, $conditionUser)));
            $paymentTerms = ClassRegistry::init('PaymentTerm')->find('list', array('conditions' => array('is_active = 1'), 'order' => 'name'));
            $code = $this->Helper->getAutoGenerateCustomerCode();
            $this->set(compact('paymentTerms', 'cgroups', "companies", "code"));
        }
    }
    
    function disByCard() {
        $this->layout = 'ajax';
    }
    
    function discountByItem(){
        $this->layout = 'ajax';
    }
    
    function customerDisplay(){
        $this->layout = 'pos_display';
    }
    
    function saveItemVoid(){
        $this->layout = 'ajax';
        if(!empty($_POST['product_id']) && isset($_POST['qty']) && !empty($_POST['uom_id'])){
            $this->loadModel('PosVoid');
            $user = $this->getCurrentUser();
            $dateNow   = date("Y-m-d H:i:s");
            $this->PosVoid->create();
            $this->data['PosVoid']['product_id'] = $_POST['product_id'];
            $this->data['PosVoid']['qty']        = $_POST['qty'];
            $this->data['PosVoid']['uom_id']     = $_POST['uom_id'];
            $this->data['PosVoid']['created']    = $dateNow;
            $this->data['PosVoid']['created_by'] = $user['User']['id'];
            $this->PosVoid->save($this->data);
        }
        exit;
    }

    

}

?>