<?php
class SendCloud_Integration_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'sendcloudcarrier';

    // public function getFormBlock()
    // {
    //     return 'sendcloud/servicepointpicker';
    // }

    public function getAllowedMethods()
    {
        return array(
            $this->_code    =>  $this->getConfigData('methodname'),
            // 'boat'    =>  'Via boat',
            // 'pigeon'    =>  'Duivenpost',
        );
    }

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = Mage::getModel('shipping/rate_result');

        $subtotal = $request->getPackageValueWithDiscount();
        if ($this->getConfigData('enabled') == 1) {
            //Check for allowed countries
            $allow = $this->getConfigData('sallowspecific');
            $availableCountries = array();
            if($allow) {
                $availableCountries = explode(',', $this->getConfigData('specificcountry'));
            }

            if ($availableCountries && !in_array($request->getDestCountryId(), $availableCountries)) {
                return $result;
            }

            $rate = $this->_getDefaultRate();
            if($request->getFreeShipping() || ($this->getConfigData('free_shipping_enable') && $subtotal >= $this->getConfigData('free_shipping_subtotal'))) {
                $rate->setPrice((int) 0);
            }
            $result->append($rate);
        }

        return $result;
    }

    protected function _getDefaultRate()
    {
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod($this->_code);
        $rate->setMethodTitle($this->getConfigData('methodname'));
        // $rate->setMethodTitle($this->getConfigData('name'));
        $rate->setPrice($this->getConfigData('price'));
        $rate->setCost(0);

        return $rate;
    }

    public function isTrackingAvailable()
    {
        return false;
    }

    /*public function getTrackingInfo($tracking)
    {
        $track = Mage::getModel('shipping/tracking_result_status');
        $track->setUrl($tracking)
          ->setTracking($tracking)
          ->setCarrierTitle($this->getConfigData('title'));
        return $track;
    }*/
}
