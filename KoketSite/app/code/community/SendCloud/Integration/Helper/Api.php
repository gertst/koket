<?php
/**
 * Created by PhpStorm.
 * User: nanne
 * Date: 28-05-15
 * Time: 12:52
 */
require_once(Mage::getBaseDir('lib') . '/SendCloud/SendCloudApi.php');

class SendCloud_Integration_Helper_API extends Mage_Core_Helper_Data
{
    public function __construct()
    {
        $publickey = Mage::getStoreConfig('sendcloudintegration/apisettings/publickey');
        $secretkey = Mage::getStoreConfig('sendcloudintegration/apisettings/secretkey');
        $this->api = new SendCloudApi($publickey, $secretkey);
    }

    public function lastParcels() {
        return $this->api->parcels->get();
    }

    public function transfer($order) {
        $address = $order->getShippingAddress();

        $send = array(
                    'name' => $address->getName(),
                    'company_name' => $address->getCompany(),
                    'address' => $address->getStreet(1),
                    'city' => $address->getCity(),
                    'postal_code' => $address->getPostcode(),
                    'telephone' => $address->getTelephone(),
                    'email' => $address->getEmail() ?: $order->getCustomerEmail(),
                    'country' => Mage::getModel('directory/country')->load($address->getCountry())->getIso2Code(),
                    'order_number' => (int)$order->getRealOrderId()
                );

        /**
         * Necessary code for service points, hopefully this will be changed in the future
         */
        if($order->getData('sendcloud_service_point')) {
            $send['data'] = json_decode($order->getData('sendcloud_service_point'));

            if(in_array("pakjegemak", array_keys((array)$send['data']))) {
                $send["shipment"] = array("id" => 7);
            }

            //GERT
            // Add the comment and save the order (last parameter will determine if comment will be sent to customer)
            $order->addStatusHistoryComment('SendCloud Info: ' . $order->getData('sendcloud_service_point'));
            $order->save();



        }





        // housenumber help
        $toHousenumber = Mage::getStoreConfig('sendcloudintegration/configuration/addresstwoashousenumber');
        if($toHousenumber) {
            $send['house_number'] = $address->getStreet(2);
        } else {
            $send['address_2'] = $address->getStreet(2);
        }

        $parcel = $this->api->parcels->create($send);

        // update info into database TODO: move to shipment
        $updater = Mage::helper('sendcloud_integration/updater');
        $updater->update($order, $parcel['id']);

        // create shipment
        $helper = Mage::helper('sendcloud_integration/shipment');
        $helper->createShipmentFromOrder($order, $parcel);

        return $parcel;
    }
}
