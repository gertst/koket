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
 * RewardPointsRule Calculation Earning Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Helper_Calculation_Earning extends Magestore_RewardPoints_Helper_Calculation_Earning {

    const XML_PATH_EARNING_BY_SHIPPING = 'rewardpoints/earning/by_shipping';
    const XML_PATH_EARNING_BY_TAX = 'rewardpoints/earning/by_tax';

    /**
     * get calculate earning point for each product
     * 
     * @param mixed $product
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $date
     * @return int
     */
    public function getCatalogEarningPoints($product, $customerGroupId = null, $websiteId = null, $date = null) {
        if (!is_object($product) and is_numeric($product)) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        if (is_null($customerGroupId)) {
            if ($product->hasCustomerGroupId()) {
                $customerGroupId = $product->getCustomerGroupId();
            } else {
                $customerGroupId = $this->getCustomerGroupId();
            }
        }
        if (is_null($websiteId)) {
            $websiteId = $this->getWebsiteId();
        }
        $cacheKey = "catalog_earning:{$product->getId()}:$customerGroupId:$websiteId";
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        $points = 0;
        $collectionKey = "catalog_earning_collection:$customerGroupId:$websiteId";
        if (!$this->hasCache($collectionKey)) {
            $rules = Mage::getResourceModel('rewardpointsrule/earning_catalog_collection')
                    ->setAvailableFilter($customerGroupId, $websiteId, $date);
            foreach ($rules as $rule) {
                $rule->afterLoad();
            }
            $this->saveCache($collectionKey, $rules);
        } else {
            $rules = $this->getCache($collectionKey);
        }
        foreach ($rules as $rule) {
            /**
             * end update
             */
            if ($rule->validate($product)) {
                $points += $this->calcCatalogPoint(
                        $rule->getSimpleAction(), $rule->getPointsEarned(), Mage::helper('tax')->getPrice($product, $product->getFinalPrice()), Mage::helper('tax')->getPrice($product, $product->getFinalPrice()) - $product->getCost(),
//                                $product->getPrice(),
//                                $product->getPrice() - $product->getCost(),
                        $rule->getMoneyStep(), $rule->getMaxPointsEarned()
                );
                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
        }
        $this->saveCache($cacheKey, $points);
        return $this->getCache($cacheKey);
    }

    /**
     * calculate earning for quote/order item
     * 
     * @param Varien_Object $item
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $date
     * @return int
     */
    public function getCatalogItemEarningPoints($item, $customerGroupId = null, $websiteId = null, $date = null) {
        $product = Mage::getModel('catalog/product')->load($item->getProductId());
        if (is_null($customerGroupId)) {
            if ($product->hasCustomerGroupId()) {
                $customerGroupId = $product->getCustomerGroupId();
            } else {
                $customerGroupId = $this->getCustomerGroupId();
            }
        }
        if (is_null($websiteId)) {
            $websiteId = Mage::app()->getStore($item->getStoreId())->getWebsiteId();
        }
        if (is_null($date)) {
            $date = date('Y-m-d', strtotime($item->getCreatedAt()));
        }
        $cacheKey = "catalog_item_earning:{$item->getId()}:$customerGroupId:$websiteId";
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        $points = 0;
        $collectionKey = "catalog_earning_collection:$customerGroupId:$websiteId";
        if (!$this->hasCache($collectionKey)) {
            $rules = Mage::getResourceModel('rewardpointsrule/earning_catalog_collection')
                    ->setAvailableFilter($customerGroupId, $websiteId, $date);
            foreach ($rules as $rule) {
                $rule->afterLoad();
            }
            $this->saveCache($collectionKey, $rules);
        } else {
            $rules = $this->getCache($collectionKey);
        }

        $session = Mage::getSingleton('checkout/session');
        $catalogRules = $session->getCatalogRules();
        if (is_array($catalogRules) && isset($catalogRules[$item->getId()])) {
            $catalog = $catalogRules[$item->getId()]['point_discount'] / Mage::app()->getStore($item->getStoreId())->convertPrice(1);
        } else
            $catalog = 0;

        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
            $price = 0;
            $profit = 0;
            foreach ($item->getChildren() as $child) {
                $price += $child->getQty() * ($child->getBasePrice() - $catalog);
                $profit += $child->getQty() * ($child->getBasePrice() - $child->getBaseCost());
            }
        } else {
            $price = $item->getBasePrice();
            if (!$price && $item->getPrice()) {
                $price = $item->getPrice() / Mage::app()->getStore($item->getStoreId())->convertPrice(1);
            }
            $profit = $price - $item->getBaseCost();
            $price -= $catalog;
        }
        foreach ($rules as $rule) {
            /**
             * end update
             */
            if ($rule->validate($product)) {
                $points += $this->calcCatalogPoint(
                        $rule->getSimpleAction(), $rule->getPointsEarned(), $price, $profit, $rule->getMoneyStep(), $rule->getMaxPointsEarned()
                );
                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
        }
        $this->saveCache($cacheKey, $points * $item->getQty());
        return $this->getCache($cacheKey);
    }

    /**
     * Calculate points for product by Catalog Rule
     * 
     * @param type $actionOperator is action type when chose at action in created rule
     * @param type $xAmount
     * @param type $price
     * @param type $profit
     * @param type $yStep
     * @param type $maxAmount is max amount could earned when was input ago
     * @return int
     */
    public function calcCatalogPoint($actionOperator, $xAmount, $price, $profit, $yStep, $maxAmount) {
        $points = 0;
        switch ($actionOperator) {
            case 'fixed':
                $points = $xAmount;
                break;
            case 'by_price':
                if ($yStep > 0) {
                    $points = $this->round($price / $yStep) * $xAmount;
                    if ($maxAmount && $points > $maxAmount) {
                        $points = $maxAmount;
                    }
                }
                break;
            case 'by_profit':
                if ($yStep > 0) {
                    $points = $this->round($profit / $yStep) * $xAmount;
                    if ($maxAmount && $points > $maxAmount) {
                        $points = $maxAmount;
                    }
                }
                break;
        }
        return (int) $points;
    }

    /**
     * calculate earning point for order quote
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $date
     * @return int
     */
    public function getShoppingCartPoints($quote, $customerGroupId = null, $websiteId = null, $date = null) {
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        if (is_null($customerGroupId)) {
            $customerGroupId = $quote->getCustomerGroupId();
        }
        if (is_null($websiteId)) {
            $websiteId = Mage::app()->getStore($quote->getStoreId())->getWebsiteId();
        }
        if (is_null($date)) {
            $date = date('Y-m-d', strtotime($quote->getCreatedAt()));
        }
        $points = 0;

        $rules = Mage::getResourceModel('rewardpointsrule/earning_sales_collection')
                ->setAvailableFilter($customerGroupId, $websiteId, $date);
        $items = $quote->getAllItems();
        $this->setStoreId($quote->getStoreId());
        foreach ($rules as $rule) {
            /**
             * end update
             */
            $rule->afterLoad();
            if (!$rule->validate($address)) {
                continue;
            }
            $rowTotal = 0;
            $qtyTotal = 0;
            foreach ($items as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                if ($rule->getActions()->validate($item)) {
                    $rowTotal += max(0, $item->getBaseRowTotal() - $item->getBaseDiscountAmount() - $item->getRewardpointsBaseDiscount());
                    $qtyTotal += $item->getQty();
                }
            }
            if (!$qtyTotal) {
                continue;
            }
            //Hai.Tran 21/11
            if (Mage::getStoreConfigFlag(self::XML_PATH_EARNING_BY_SHIPPING, $quote->getStoreId())) {
                $rowTotal += $address->getBaseShippingAmount() - $address->getRewardpointsBaseAmount();
            }
            if (Mage::getStoreConfigFlag(self::XML_PATH_EARNING_BY_TAX, $quote->getStoreId())) {
                $rowTotal += $address->getBaseTaxAmount();
            }
            //End Hai.Tran
            $points += $this->calcSalesPoints(
                    $rule->getSimpleAction(), $rule->getPointsEarned(), $rule->getMoneyStep(), $rowTotal, $rule->getQtyStep(), $qtyTotal, $rule->getMaxPointsEarned()
            );
            if ($points && $rule->getStopRulesProcessing()) {
                break;
            }
        }
        return $points;
    }

    /**
     * Calculate the point received for shopping cart rule
     * 
     * @param string $pointOperation
     * @param float $xAmount
     * @param float $yStep
     * @param float $price
     * @param int $qtyStep
     * @param float $qty
     * @param int $maxPoint
     * @return int
     */
    public function calcSalesPoints($pointOperation, $xAmount, $yStep, $price, $qtyStep, $qty, $maxPoint) {
        $points = 0;
        switch ($pointOperation) {
            case 'fixed':
                $points = $xAmount;
                break;
            case 'by_total':
                if ($yStep > 0) {
                    $points = $this->round($price / $yStep) * $xAmount;
                    if ($maxPoint && $points > $maxPoint) {
                        $points = $maxPoint;
                    }
                }
                break;
            case 'by_qty':
                if ($qtyStep > 0) {
                    $points = $this->round($qty / $qtyStep) * $xAmount;
                    if ($maxPoint && $points > $maxPoint) {
                        $points = $maxPoint;
                    }
                }
                break;
        }
        return (int) $points;
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

}
