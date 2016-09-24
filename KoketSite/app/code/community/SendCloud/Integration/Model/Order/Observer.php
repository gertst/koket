<?php
/**
  * This observer arranges the automatic transfer to SendCloud
  */
class SendCloud_Integration_Model_Order_Observer {

    public function after_order_save($observer) {
        // retrieve order
        $event = $observer->getEvent();
        $order = $observer->getOrder();

        // // check for automatic transfer
        // $canTransfer = Mage::getStoreConfig('sendcloudintegration/configuration/automatictransfer');
        // if(!$canTransfer) return;

        // check for transfer condition
        $transferCondition = Mage::getStoreConfig('sendcloudintegration/process/transfercondition');
        if(!$transferCondition) {
            return;
        }

        if($order->getStatus() != $transferCondition) return;

        // lock order, only needed for automatic transfer
        $process = new Mage_Index_Model_Process();
        $process->setId("sendcloud_".$order->getId());
        $process->lockAndBlock();

        // next lock
        $updater = Mage::helper('sendcloud_integration/updater');
        $parcel_id = $updater->check($order);

        if(empty($parcel_id)) {
            try {
                // transfer to SendCloud
                $api = Mage::helper('sendcloud_integration/api');
                $api->transfer($order);
            } catch(SendCloudApiException $excep) {
                // log potential exceptions
                Mage::log($excep->getMessage(), Zend_Log::ERR);
            }
        }
        $process->unlock();
    }

    public function check_parcels() {
        $shipmentModel = Mage::getModel("sales/order_shipment");
        $shipmentCollection = $shipmentModel->getCollection();
        $shipmentCollection->getSelect()
          ->limit(100)
          ->order('updated_at desc')
          ->where('sendcloud_parcel_id is not null');

        if(count($shipmentCollection) == 0) return;

        $announceStatus = Mage::getStoreConfig('sendcloudintegration/process/announcestatuschange');
        $deliveryStatus = Mage::getStoreConfig('sendcloudintegration/process/deliverystatuschange');
        // 999, ready to be announced
        $status_options = array(
          1000, // ready to send
          93, 92, 91, 80, 22, 18, 15, 13, 12, 7, 6, 5, 3, // random statusses within shipping
          11, //delivered
          8, // delivery attempt failed
          1 // announced
        );

        $shipments = array();
        foreach ($shipmentCollection as $value) {
            $parcel_id = $value->getData('sendcloud_parcel_id');

            $shipments[$parcel_id] = $value;
        }

        $api = Mage::helper('sendcloud_integration/api');
        $parcels = $api->lastParcels();

        foreach ($parcels as $parcel) {
            $parcel_id = $parcel['id'];

            if(array_key_exists($parcel_id, $shipments)) {
                $shipment = $shipments[$parcel_id];
                $old_status_id = $shipment->getData('sendcloud_parcel_status_id');
                $new_status_id = $parcel['status']['id'];

                if($parcel["tracking_number"] != "") {
                    $containing = false;
                    $tracking = $shipment->getAllTracks();
                    foreach ($tracking as $value) {
                        if($value->getNumber() == $parcel["tracking_number"]){
                            $containing = true;
                        }
                    }
                    if(!$containing) {
                        $trackdata = array();
                        $trackdata['carrier_code'] = "custom";
                        $trackdata['title'] = "tracking_number";
                        $trackdata['number'] = $parcel["tracking_number"];
                        //$trackdata['track_link'] = $data[4];

                        $track = Mage::getModel('sales/order_shipment_track')->addData($trackdata);
                        $shipment->addTrack($track);
                        $shipment->save();
                    }
                }

                // if announced
                if($old_status_id == 999 && in_array($new_status_id, $status_options) && $new_status_id != 11) {
                    $shipment->setData("sendcloud_parcel_status_id", $new_status_id);
                    $shipment->save();
                    // 
                    // Mage::getModel('sales/order_shipment')
                    //   ->load($shipment->getEntityId())
                    //   ->setData('error', 'Invalid price.')
                    //   ->save();

                    if($announceStatus) {
                        $order = $shipment->getOrder();
                        $order->setStatus($announceStatus);
                        $order->save();
                    }
                }

                if($old_status_id != $new_status_id && $new_status_id == 11) {
                    $shipment->setData('sendcloud_parcel_status_id', $new_status_id);
                    $shipment->save();
                    if($deliveryStatus) {
                        $order = $shipment->getOrder();
                        $order->setStatus($deliveryStatus);
                        $order->save();
                    }
                }
            }
        }
    }
}
