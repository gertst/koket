<?php

class SendCloud_Integration_Helper_Shipment extends Mage_Core_Helper_Data {
    public function createShipmentFromOrder($order, $parcel)
    {
        $convertor = Mage::getModel('sales/convert_order');
        $shipment = $convertor->toShipment($order);
        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getIsVirtual()) { // $orderItem->getQtyToShip() &&
                $item = $convertor->itemToShipmentItem($orderItem);
                $item->setQty($orderItem->getQtyToShip());
                $shipment->addItem($item);
            }
        }

        // $track = Mage::getModel('sales/order_shipment_track')->addData($trackdata);
        // $shipment->addTrack($track);

        $shipment->register();

        $sendEmail = Mage::getStoreConfig('sendcloudintegration/extrafunctionality/notifycustomer') == true; // make sure a boolean returns

        try {
            $shipment->sendEmail($sendEmail)
              ->setEmailSent($sendEmail)
              ->setData('sendcloud_parcel_id', $parcel['id']);
            $shipment->setData('sendcloud_parcel_status_id', 999);

            $shipment->save();
            Mage::getModel('core/resource_transaction')
                     ->addObject($shipment)
                     ->addObject($order)
                     ->save();
             $order->addStatusToHistory($order->getStatus(), Mage::helper('sendcloud_integration')->__('Order completed, sent with Sendcloud Integration'), false);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError(
              "[SendCloud] ".
              Mage::helper('sendcloud_integration')->__('Order %s has problems creating an extra shipment: ', "".$order->getIncrementId())
              .  $e->getMessage());
        }
    }
}
