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
 * RewardPoints Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLE = 'rewardpoints/general/enable';
    
    /**
     * check reward points system is enabled
     * 
     * @param mixed $store
     * @return boolean
     */
    public function isEnable($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE, $store);
    }
    
    /**
     * get rewards points label to show on Account Navigation
     * 
     * @return string
     */
    public function getMyRewardsLabel()
    {
        $pointAmount = Mage::helper('rewardpoints/customer')->getBalance();
        if ($pointAmount > 0) {
            $rate = Mage::getModel('rewardpoints/rate')->getRate(Magestore_RewardPoints_Model_Rate::POINT_TO_MONEY);
            if ($rate && $rate->getId()) {
                $baseAmount = $pointAmount * $rate->getMoney() / $rate->getPoints();
                $pointAmount = Mage::app()->getStore()->convertPrice($baseAmount, true);
            }
        }
        $imageHtml = Mage::helper('rewardpoints/point')->getImageHtml(false);
        return $this->__('My Rewards') . ' ' . $pointAmount . $imageHtml;
    }
    
    public function getItemRateOnQuote($product, $store){
        //Calculate rate to subtract taxable amount
        $priceIncludesTax = (bool)Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $store);
        $taxClassId = $product->getTaxClassId();
        if ($taxClassId && $priceIncludesTax) {
            $request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false, $store);
            $rate = Mage::getSingleton('tax/calculation')
                ->getRate($request->setProductClassId($taxClassId));
            $rate = 1 + $rate/100;
        }else $rate = 1;
        return $rate;
    }
}
