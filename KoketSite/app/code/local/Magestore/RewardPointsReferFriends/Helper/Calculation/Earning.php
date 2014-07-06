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
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPoints Earning Calculation Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Helper_Calculation_Earning extends Magestore_RewardPoints_Helper_Calculation_Earning {

    /**
     * get commission points for referral
     * @param type $address
     * @return type string
     */
    public function getCommitionEarningPoints($address) {
        $cacheKey = "offer_commission_points";
        if (!$this->hasCache($cacheKey)) {
            $this->saveCache($cacheKey, $this->calculateCommissionPoints($address));
        }
        return $this->getCache($cacheKey);
    }

    /**
     * calculate commission points for referal
     * @param type $address
     * @return int
     */
    public function calculateCommissionPoints($address, $customerGroupId = null, $websiteId = null, $date = null) {

        $key = Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key');

        if (!$key)
            return 0;
        $refer_cus = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->loadByKey($key);
        $quote = $address->getQuote();
        if (!$this->checkUsesPerCustomer($quote))
            return 0;
        if (!$refer_cus || !$refer_cus->getId() || Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($quote->getCustomerEmail())->getId() == $refer_cus->getId())
            return 0;
        if (is_null($customerGroupId)) {
            $customerGroupId = $quote->getCustomerGroupId();
        }
        if (is_null($websiteId)) {
            $websiteId = Mage::app()->getStore($quote->getStoreId())->getWebsiteId();
        }
        if (is_null($date)) {
            $date = $quote->getCreatedAt();
        }
        $points = 0;
        $offers = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer')->loadByCustomerOrder($address, $customerGroupId, $websiteId, $date);

        if (count($offers)) {

            foreach ($offers as $offer) {

//                $total = 0;
                $items = 0;
                foreach ($quote->getItemsCollection() as $item) {
                    if ($item->getParentItemId()) {

                        continue;
                    }
                    if (!$item->isDeleted()) {

                        if ($offer->getActions()->validate($item)) {
//                            $total += $item->getBasePrice() * $item->getQty();
                            $items+=$item->getQty();
                        }
                    }
                }
//                if (version_compare(Mage::getVersion(), '1.6.0.0', '>='))
                $total = $address->getBaseGrandTotal();
//                echo $total;
//                else
//                    $total = $address->getBaseSubtotalWithDiscount();
                if ($offer->getCommissionAction() == Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_ACTION_GIVE_POINT_TO_CUSTOMER) {

                    if ($items) {

                        $points += (int) $offer->getCommissionPoint();
                    }
                } else if ($offer->getCommissionAction() == Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_ACTION_GIVE_POINT_EVERY_MONEY) {
                    $points += (int) $offer->getCommissionPoint() * $this->round($total / $offer->getMoneyStep());
                }
                else
                    $points += (int) $offer->getCommissionPoint() * $this->round($items / $offer->getQtyStep());
                if ($points && $items && $offer->getStopRulesProcessing()) {
                    break;
                }
            }

            return $points;
        } else if (Mage::helper('rewardpointsreferfriends')->getReferConfig('use_default_config')) {
            if (Mage::helper('rewardpointsreferfriends')->getReferConfig('earn_points'))
                return (int) $this->round(Mage::helper('rewardpointsreferfriends')->getReferConfig('earn_points'));
        }

        return 0;
    }

    /**
     * get discount for invited person
     * @param type $address
     * @return type
     */
    public function getInvitedDiscount($address) {
        $cacheKey = "offer_invited_discount";
        if (!$this->hasCache($cacheKey)) {
            $this->saveCache($cacheKey, $this->calculateInvitedDiscount($address));
        }
        return $this->getCache($cacheKey);
    }

    /**
     * calculate discount for invited person
     * @param type $address
     * @return int
     */
    public function calculateInvitedDiscount($address, $customerGroupId = null, $websiteId = null, $date = null) {
        $key = Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key');
        if (!$key)
            return 0;
        $refer_cus = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->loadByKey($key);
        $quote = $address->getQuote();
        if (!$this->checkUsesPerCustomer($quote)) {
            $type = Mage::helper('rewardpointsreferfriends')->__('link/coupon');
            Mage::getSingleton('checkout/session')->addError(Mage::helper('rewardpointsreferfriends')->__('You have used %s too many times to allow ', $type));
            return 0;
        }

        if (!$refer_cus || !$refer_cus->getId() || Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($quote->getCustomerEmail())->getId() == $refer_cus->getId())
            return 0;
        if (is_null($customerGroupId)) {
            $customerGroupId = $quote->getCustomerGroupId();
        }
        if (is_null($websiteId)) {
            $websiteId = Mage::app()->getStore($quote->getStoreId())->getWebsiteId();
        }
        if (is_null($date)) {
            $date = $quote->getCreatedAt();
        }
        $discount = 0;
        $offers = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer')->loadByCustomerOrder($address, $customerGroupId, $websiteId, $date);
//                foreach ($offers as $offer) {
//                    $offer->afterLoad();
//        echo count($offers);
        if (count($offers)) {
            foreach ($offers as $offer) {

                $total = 0;
                $items = 0;
                foreach ($quote->getItemsCollection() as $item) {
                    if ($item->getParentItemId()) {

                        continue;
                    }
                    if (!$item->isDeleted()) {

                        if ($offer->getActions()->validate($item)) {
                            $total += $item->getBasePrice() * $item->getQty();
                            $items+=$item;
                        }
                    }
                }

                if ($offer->getDiscountType() == Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_TYPE_FIXED) {
                    if ($items)
                        $discount +=$offer->getDiscountValue();
                } else {
                    $discount += $total * $offer->getDiscountValue() / 100;
                }
                if ($discount && $items && $offer->getStopRulesProcessing()) {
                    break;
                }
            }
            return $discount;
        } else if (Mage::helper('rewardpointsreferfriends')->getReferConfig('use_default_config')) {
            $total = 0;
            foreach ($quote->getItemsCollection() as $item) {
                if (!$item->isDeleted()) {
                    $total += $item->getBasePrice() * $item->getQty();
                }
            }
            if (Mage::helper('rewardpointsreferfriends')->getReferConfig('discount_type') == 'fix') {
                return Mage::helper('rewardpointsreferfriends')->getReferConfig('discount_value');
            } else {
                return $total * Mage::helper('rewardpointsreferfriends')->getReferConfig('discount_value') / 100;
            }
        }

        return 0;
    }

    /**
     * set store id for current working helper
     * 
     * @param int $value
     * @return Magestore_RewardPointsRule_Helper_Calculation_Earning
     */
    public function setStoreId($value) {
        $this->saveCache('store_id', $value);
        return $this;
    }

    public function round($number) {
        return Mage::helper('rewardpoints/calculator')->round(
                        $number, $this->getCache('store_id')
        );
    }

    /**
     * check uses per customer when offer
     * @param type $quote
     * @return boolean
     */
    public function checkUsesPerCustomer($quote) {
        $customer_id = $quote->getCustomerId();
        $uses_per_customer = Mage::helper('rewardpointsreferfriends')->getUsesPerCustomer();

        if ($customer_id && $uses_per_customer) {
            $collection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_id', $customer_id)
                    ->addFieldToFilter('rewardpoints_invited_discount', array('gt'=>0));
            if (count($collection) >= $uses_per_customer) {
                

                return false;
            }
        }
        return true;
    }
}
