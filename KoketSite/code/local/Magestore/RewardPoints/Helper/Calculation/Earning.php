<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPoints Earning Calculation Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Helper_Calculation_Earning extends Magestore_RewardPoints_Helper_Calculation_Abstract {

    const XML_PATH_EARNING_EXPIRE = 'rewardpoints/earning/expire';
    const XML_PATH_EARNING_ORDER_INVOICE = 'rewardpoints/earning/order_invoice';
    const XML_PATH_HOLDING_DAYS = 'rewardpoints/earning/holding_days';
    const XML_PATH_ORDER_CANCEL_STATUS = 'rewardpoints/earning/order_cancel_state';
    const XML_PATH_EARNING_BY_SHIPPING = 'rewardpoints/earning/by_shipping';
    const XML_PATH_EARNING_BY_TAX = 'rewardpoints/earning/by_tax';

    /**
     * get Total Point that customer can earn by purchase current order/ quote
     * 
     * @param null|Mage_Sales_Model_Quote $quote
     * @return int
     */
    public function getTotalPointsEarning($quote = null) {
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        //Hai.Tran 21/11
        $grandTotal = $quote->getBaseGrandTotal();

        if (!Mage::getStoreConfigFlag(self::XML_PATH_EARNING_BY_SHIPPING, $quote->getStoreId())) {
            $grandTotal -= $address->getBaseShippingAmount();
            //added by GERT: the TAX on shipping should not be included in earning points, so subtract TAX (21%) on shipping amount
            $grandTotal -= ($address->getBaseShippingAmount() * .21);
        }
        if (!Mage::getStoreConfigFlag(self::XML_PATH_EARNING_BY_TAX, $quote->getStoreId())) {
            $grandTotal -= $address->getBaseTaxAmount();
        }
        $grandTotal = max(0, $grandTotal);
        //end Hai.Tran 21/11
        $container = new Varien_Object(array(
            'total_points' => $this->getRateEarningPoints($grandTotal, $quote->getStoreId()),
        ));
        Mage::dispatchEvent('rewardpoints_calculation_earning_total_points', array(
            'quote' => $quote,
            'container' => $container,
        ));



        return $container->getTotalPoints();
    }
    public function getEarningPointByCoupon($quote = null){
        $needConvert = Mage::getStoreConfig('rewardpoints/general/convert_point');
        if(!$needConvert) return 0;
        
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        return $address->getRewardpointsPointsByDiscount();
    }
    public function getCouponEarnPoints($quote = null){
        $needConvert = Mage::getStoreConfig('rewardpoints/general/convert_point');
        if(!$needConvert) return 0;
        
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        return $address->getCouponCode();
    }

    /**
     * calculate quote earning points by system rate
     * 
     * @param float $baseGrandTotal
     * @param mixed $store
     * @return int
     */
    public function getRateEarningPoints($baseGrandTotal, $store = null) {
        $customerGroupId = $this->getCustomerGroupId();
        $websiteId = $this->getWebsiteId();

        $cacheKey = "earning_rate_points:$customerGroupId:$websiteId:$baseGrandTotal";
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        $rate = Mage::getSingleton('rewardpoints/rate')->getRate(
                Magestore_RewardPoints_Model_Rate::MONEY_TO_POINT, $customerGroupId, $websiteId
        );
        if ($rate && $rate->getId()) {
            /**
             * end update
             */
            if ($baseGrandTotal < 0) {
                $baseGrandTotal = 0;
            }
            $points = Mage::helper('rewardpoints/calculator')->round(
                    $baseGrandTotal * $rate->getPoints() / $rate->getMoney(), $store
            );

            //gert
/*
            Mage::log(
                $points . ' - $baseGrandTotal: ' . $baseGrandTotal . ' - $rate->getPoints(): ' . $rate->getPoints() . ' - $rate->getMoney(): ' . $rate->getMoney(),
                Zend_Log::DEBUG,  //Log level
                'stogo.log',         //Log file name; if blank, will use config value (system.log by default)
                true              //force logging regardless of config setting
            );
*/

            $this->saveCache($cacheKey, $points);
        } else {
            $this->saveCache($cacheKey, 0);
        }
        return $this->getCache($cacheKey);
    }

    /**
     * get current checkout quote
     * 
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote() {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }
        return Mage::getSingleton('checkout/session')->getQuote();
    }

}