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
 * @package     Magestore_RewardPointsRule
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsRule Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsRule
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Model_Frontend_Observer
{
    /**
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Frontend_Observer
     */
    public function dashboardCanShowEarning($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $container = $observer['container'];
        if (!$container->getCanShow()) {
            $block = Mage::getBlockSingleton('rewardpointsrule/account_dashboard_earn');
            if (count($block->getCatalogRules()) || count($block->getShoppingCartRules())) {
                $container->setCanShow(true);
            }
        }
        return $this;
    }
    
    /**
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Frontend_Observer
     */
    public function dashboardCanShowSpending($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $container = $observer['container'];
        if (!$container->getCanShow()) {
            $block = Mage::getBlockSingleton('rewardpointsrule/account_dashboard_spend');
            if (count($block->getCatalogRules()) || count($block->getShoppingCartRules())) {
                $container->setCanShow(true);
            }
        }
        return $this;
    }
    
    /**
     * Check to show reward points core earning on product
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Frontend_Observer
     */
    public function showEarningOnProduct($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $container = $observer['container'];
        if ($container->getEnableDisplay()) {
            $block = Mage::getBlockSingleton('rewardpointsrule/product_view_earning');
            if ($block->getEarningPoints() > 0) {
                $container->setEnableDisplay(false);
            }
        }
        return $this;
    }
}
