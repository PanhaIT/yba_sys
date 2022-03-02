<?php

class PickSlipsController extends AppController {

    var $uses = 'Delivery';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Pick Slip', 'Dashboard');
    }

    function ajax($changeBranchPickSlipSR = 'all',$changeCustomerIdPickSlipSR = 'all',$changeStatusPickSlipSR = 'all',$changeDatePickSlipSR = 'all') {
        $this->layout = 'ajax';
        $this->set(compact('changeBranchPickSlipSR','changeCustomerIdPickSlipSR','changeStatusPickSlipSR','changeDatePickSlipSR'));
    }

    function pick($id = null){
        $this->layout = 'ajax';
        if(empty($id) && empty($this->data)){
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if(!empty($this->data)){
            $delivery = ClassRegistry::init('Delivery')->find("first", array('conditions' => array('Delivery.id' => $this->data['id'])));
            if($delivery['Delivery']['status'] == 2){
                // Update Order
                mysql_query("UPDATE deliveries SET status = 3, delivered = '".date("Y-m-d H:i:s")."', delivered_by = ".$user['User']['id']." WHERE id = ".$this->data['id']);
                $this->Helper->saveUserActivity($user['User']['id'], 'Pick Slip', 'Pick', $this->data['id'], $this->data['id']);
                $result['error'] = 0;
                $result['id'] = $this->data['id'];
                echo json_encode($result);
                exit;
            } else {
                $this->Helper->saveUserActivity($user['User']['id'], 'Pick Slip', 'Pick (Error Status)', $this->data['id'], $this->data['id']);
                $result['error'] = 1;
                $result['id'] = "";
                echo json_encode($result);
                exit;
            }
        }
        $delivery = ClassRegistry::init('Delivery')->find("first", array('conditions' => array('Delivery.id' => $id)));
        if (!empty($delivery)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Pick Slip', 'View', $id);
            $salesOrder      = ClassRegistry::init('SalesOrder')->find("first", array('conditions' => array('SalesOrder.delivery_id' => $id)));
            $deliveryDetails = ClassRegistry::init('DeliveryDetail')->find("all", array('conditions' => array('DeliveryDetail.delivery_id' => $id)));
            $this->set(compact('delivery', 'deliveryDetails', 'salesOrder'));
        } else {
            exit;
        }
    }

    function printInvoicePickSlip($id = null){
        $this->layout = 'ajax';
        if(empty($id)){
            exit;
        }
        $delivery = ClassRegistry::init('Delivery')->find("first", array('conditions' => array('Delivery.id' => $id)));
        if($delivery['Delivery']['status']==3){
            $salesOrder = ClassRegistry::init('SalesOrder')->find("first", array('conditions' => array('SalesOrder.delivery_id' => $id)));
            if (!empty($salesOrder)) {
                $salesOrderDetails = ClassRegistry::init('SalesOrderDetail')->find("all", array('conditions' => array('SalesOrderDetail.sales_order_id' =>$salesOrder['SalesOrder']['id'])));
                $deliveryDetails   = ClassRegistry::init('DeliveryDetail')->find("all", array('conditions' => array('DeliveryDetail.delivery_id' => $id)));
                $this->set(compact('delivery', 'deliveryDetails', 'salesOrder', 'salesOrderDetails'));
            }
        }
    }
}

?>