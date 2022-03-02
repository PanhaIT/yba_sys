<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Helper
 *
 * @author UDAYA
 */
App::import('model', 'Product');
App::import('model', 'PricingRule');
App::import('model', 'CustomerCgroup');
App::import('model', 'ProductPgroup');

class ProductComComponent extends Object {

    var $components = array('Helper');
    var $childPadding = '25';

    function productCombo() {
        $productModel = new Product();
        $products = $productModel->find('all', array('conditions' => array('Product.parent_id IS NULL', 'Product.is_active' => 1)));

        $result = '<option value="">' . INPUT_SELECT . '</option>';
        if (!empty($products)) {
            foreach ($products as $product) {
                $this->childPadding = 25;
                $result .= "<option value='{$product['Product']['id']}'>{$product['Product']['code']} - {$product['Product']['name']}</option>";
                $result .= $this->childProductCombo($product['Product']['id']);
            }
        }
        return $result;
    }

    function childProductCombo($parentId) {
        $productModel = new Product();
        $products = $productModel->find('all', array('conditions' => array('Product.parent_id' => $parentId, 'Product.is_active' => 1)));
        $result = '';
        if (!empty($products)) {
            $current = $this->childPadding;
            $this->childPadding += 25;
            foreach ($products as $product) {
                $this->childPadding = $current+25;
                $result .= "<option style='padding-left: {$current}px' value='{$product['Product']['id']}'>{$product['Product']['code']} - {$product['Product']['name']}</option>";
                $result .= $this->childProductCombo($product['Product']['id']);
            }
        }
        return $result;
    }

    function getPricingRuleOfProduct($product_id, $customer_id, $order_date) {
        $pricingRuleModel = new PricingRule();
        
        $customerGroup = $this->getCustomerGroupByCustomerId($customer_id);
        $productGroup = $this->getProductGroupByProductId($product_id);
        $productParent = $this->getProductParentByProductId($product_id);

        $customerGroup = trim($customerGroup) == "" ? "''" : $customerGroup;
        $productGroup = trim($productGroup) == "" ? "''" : $productGroup;
        $productParent = trim($productParent) == "" ? "''" : $productParent;

        $query = "
            SELECT PricingRule.* FROM pricing_rules AS PricingRule
            WHERE PricingRule.is_active = 1
                AND ((PricingRule.apply_to_customer_id IS NULL AND PricingRule.apply_to_cgroup_id IS NULL) OR PricingRule.apply_to_customer_id = '$customer_id' OR PricingRule.apply_to_cgroup_id IN ($customerGroup))
                AND ((PricingRule.apply_to_product_id IS NULL AND PricingRule.apply_to_pgroup_id IS NULL AND PricingRule.apply_to_product_parent_id IS NULL) OR PricingRule.apply_to_product_id = $product_id OR PricingRule.apply_to_pgroup_id IN ($productGroup) OR  PricingRule.apply_to_product_parent_id IN ($productParent))
                AND (PricingRule.apply_to_date = '0' OR '$order_date' BETWEEN DATE(PricingRule.start_date) AND DATE(PricingRule.end_date))
                ORDER BY PricingRule.id DESC";
        $pricingRules = $pricingRuleModel->query($query);
        return $pricingRules;
    }

    function getCustomerGroupByCustomerId($customerId) {
        $result = " ";
        $cusotmerCgroupModel = new CustomerCgroup();
        $cusotmerCgroups = $cusotmerCgroupModel->find("all",
                        array(
                            "conditions" => array("CustomerCgroup.customer_id" => $customerId)
                        )
        );
        foreach ($cusotmerCgroups as $cusotmerCgroup) {
            $result .= $cusotmerCgroup['CustomerCgroup']['cgroup_id'] . ",";
        }
        return substr($result, 0, strlen($result) - 1);
    }

    function getProductGroupByProductId($productId) {
        $result = " ";
        $productPgroupModel = new ProductPgroup();
        $productPgroups = $productPgroupModel->find("all",
                        array(
                            "conditions" => array("ProductPgroup.product_id" => $productId)
                        )
        );

        foreach ($productPgroups as $productPgroup) {
            $result .= $productPgroup['ProductPgroup']['pgroup_id'] . ",";
        }
        return substr($result, 0, strlen($result) - 1);
    }

    function getProductParentByProductId($productId) {
        $result = " ";
        $prductModel = new Product();
        $products = $prductModel->find("all",
                        array(
                            "fields" => array("Product.id, Product.parent_id"),
                            "conditions" => array("Product.id" => $productId)
                        )
        );
        foreach ($products as $product) {
            $result .= $product['Product']['id'] . ",";
            if (!empty($product['Product']['parent_id'])) {
                $result .= $this->getProductParent($product['Product']['parent_id']);
            }
        }
        $len = strlen($result) - 1;
        return (substr($result, $len, $len) == ",") ? substr($result, 0, $len) : $result;
    }

    function getProductChildByProductId($productId) {
        $result = " ";
        $prductModel = new Product();
        $products = $prductModel->find("all",
                        array(
                            "fields" => array("Product.id", "Product.parent_id"),
                            "conditions" => array("Product.parent_id" => $productId)
                        )
        );
        foreach ($products as $product) {
            if (!empty($product['Product']['parent_id'])) {
                $result .= $product['Product']['id'] . ",";
            }
            $result .= $this->getProductChildByProductId($product['Product']['id']);
        }
        $len = strlen($result) - 1;
        return (substr($result, $len, $len) == ",") ? substr($result, 0, $len) : $result;
    }

    function getProductParent($productId) {
        $result = " ";
        $prductModel = new Product();
        $products = $prductModel->find("all",
                        array(
                            "fields" => array("Product.id", "Product.parent_id"),
                            "conditions" => array("Product.id" => $productId)
                        )
        );
        foreach ($products as $product) {
            $result .= $product['Product']['id'] . ",";
            if (!empty($product['Product']['parent_id'])) {
                $result .= $this->getProductParent($product['Product']['parent_id']);
            }
        }
        $len = strlen($result) - 1;
        return (substr($result, $len, $len) == ",") ? substr($result, 0, $len) : $result;
    }

}

?>