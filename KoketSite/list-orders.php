<?php
/**
 * list orders
 * User: Gert
 * Date: 30/06/14
 * Time: 21:17
 */
//    $order->getBaseTotalDue()

require_once('app/Mage.php');
Mage::app();

//$orders = Mage::getModel('sales/order')->getCollection()
//    ->addFieldToFilter('status', 'complete')
//    ->addAttributeToSelect('customer_email')
//;
//
//
//foreach ($orders as $order) {
//    $email = $order->getCustomerEmail();
//    echo $email . "\n";
//}

$orders = Mage::getModel('sales/order')->getCollection()
    //->addFieldToFilter('status', 'complete')
    ->addAttributeToSelect('customer_email')
    ->addAttributeToSelect('status')
;

print_r($orders);

//foreach ($orders as $order) {
//    $email = $order->getCustomerEmail();
//    echo $order->getId() . ": '" . $order->getStatus() . "', " . $email . "<br>";
//}