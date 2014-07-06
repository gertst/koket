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
 * @package     Magestore_RewardPointsReferiends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpoints earn points for Order by Point Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferfriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Model_Total_Quote_Earning extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    /**
     * collect reward points that customer earned (per each item and address) total
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @param Mage_Sales_Model_Quote $quote
     * @return Magestore_RewardPointsReferFriends_Model_Total_Quote_Earning
     */
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        parent::collect($address);
        $quote = $address->getQuote();

        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        if (!Mage::helper('rewardpointsreferfriends')->isEnable($quote->getStoreId())) {
            return $this;
        }

        $key = Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key');
        if ($key) {
            $refer_cus = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->loadByKey($key);
            $address->setRewardpointsReferCustomerId($refer_cus->getCustomerId());
        }
        $invited_base_discount = Mage::helper('rewardpointsreferfriends/calculation_earning')->getInvitedDiscount($address);
        if ($invited_base_discount) {
            $invited_discount = Mage::app()->getStore()->convertPrice($invited_base_discount);
            if ($address->getBaseGrandTotal() < $invited_base_discount) {
                $invited_base_discount = $address->getBaseGrandTotal();
            }
            if ($address->getGrandTotal() < $invited_discount) {
                $invited_discount = $address->getGrandTotal();
            }
        } else {
            $invited_base_discount = 0;
            $invited_discount = 0;
        }
        $address->setRewardpointsInvitedBaseDiscount($invited_base_discount);
        $address->setRewardpointsInvitedDiscount($invited_discount);
        $address->setBaseGrandTotal($address->getBaseGrandTotal() - $invited_base_discount);
        $address->setGrandTotal($address->getGrandTotal() - $invited_discount);
        // get points that customer can earned by refer Friend
        $earningPoints = Mage::helper('rewardpointsreferfriends/calculation_earning')->getCommitionEarningPoints($address);

        if ($earningPoints > 0) {
            $address->setRewardpointsReferalEarn($earningPoints);
        }
//        echo $earningPoints;
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $amount = $address->getRewardpointsInvitedDiscount();
        if ($amount != 0) {
            $title = Mage::helper('rewardpointsreferfriends')->__('Offer Discount');
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $title,
                'value' => -$amount
            ));
        }
        return $this;
    }

}
