<?php
/**
  * This observer arranges saving of service points
  */
class SendCloud_Integration_Model_Order_ServicePoint {

    public function after_order_save($observer) {
        // retrieve order
        $event = $observer->getEvent();
        $order = $observer->getOrder();

        // check for location picker
        if(Mage::getStoreConfig('sendcloudintegration/servicepointpicker/isenabled')) {
            $post = Mage::app()->getFrontController()->getRequest()->getPost();

            Mage::getStoreConfig('sendcloudintegration/servicepointpicker/shippingmethods');

            $shippingMethod = $order->getShippingMethod();
            if(isset($shippingMethod)) {
                if(in_array($shippingMethod, explode(",", Mage::getStoreConfig('sendcloudintegration/servicepointpicker/shippingmethods')))) {
                    $session_data = Mage::getSingleton('checkout/session')->getStepData('shipping_method', 'sendcloud_service_point');

                    if($session_data) {
                        $order->setData('sendcloud_service_point', $session_data);
                    }
                    if(isset($post["location_picker"])) {
                        $order->setData('sendcloud_service_point', $post["location_picker"]);
                    }
                }
            }
        }
    }

    public function after_shippingmethod_save($observer)
    {
        // retrieve order
        $event = $observer->getEvent();
        $order = $observer->getQuote();
        // check for location picker
        if(Mage::getStoreConfig('sendcloudintegration/servicepointpicker/isenabled')) {
            $post = Mage::app()->getFrontController()->getRequest()->getPost();

            Mage::getStoreConfig('sendcloudintegration/servicepointpicker/shippingmethods');

            if(isset($post["shipping_method"], $post["location_picker"])) {
                if(in_array($post["shipping_method"], explode(",", Mage::getStoreConfig('sendcloudintegration/servicepointpicker/shippingmethods')))) {
                    Mage::getSingleton('checkout/session')->setStepData('shipping_method', 'sendcloud_service_point', $post["location_picker"]);
                }
            }
        }
    }
}
