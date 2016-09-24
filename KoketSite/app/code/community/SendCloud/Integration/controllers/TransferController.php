<?php
class SendCloud_Integration_TransferController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($order_id);
        try {
            $api = Mage::helper('sendcloud_integration/api');
            $api->transfer($order);
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('sendcloud_integration')->__('The order has succesfully been transfered to SendCloud'));
        } catch(SendCloudApiException $exception) {
            Mage::getSingleton('core/session')->addError("[SendCloud] ". $exception->getMessage());
        }

        // Return to order
        $redirect = Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id' => $order_id));
        Mage::app()->getResponse()->setRedirect($redirect);
    }

    public function massAction()
    {
        $api = Mage::helper('sendcloud_integration/api');
        foreach ($_POST['order_ids'] as $order_id) {
            $order = Mage::getModel('sales/order')->load($order_id);
            try {
                $api->transfer($order);
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('sendcloud_integration')->__('Order %s has succesfully been transfered to SendCloud', "".$order->getIncrementId()));
            } catch(SendCloudApiException $exception) {
                Mage::getSingleton('core/session')->addError(
                  "[SendCloud] ".
                  Mage::helper('sendcloud_integration')->__('Order %s has the following problem: ', "".$order->getIncrementId())
                  .  $exception->getMessage());
            }
        }

        // Return to orders view
        $redirect = Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/index");
        Mage::app()->getResponse()->setRedirect($redirect);
    }
}
