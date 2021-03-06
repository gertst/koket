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
 * RewardPoints Rewrite to fix error with Paygate Authorizenet
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Paygate_Rewrite_Authorizenet extends Mage_Paygate_Model_Authorizenet
{
    protected function _place($payment, $amount, $requestType)
    {
        /** @var $helper Magestore_RewardPoints_Helper_Calculation_Spending */
        $helper = Mage::helper('rewardpoints/calculation_spending');
        
        $rewardPointsDiscount  = $helper->getPointItemDiscount();
        $rewardPointsDiscount += $helper->getCheckedRuleDiscount();
        $rewardPointsDiscount += $helper->getSliderRuleDiscount();
        
        $container = new Varien_Object(array(
            'reward_points_discount' => $rewardPointsDiscount
        ));
        Mage::dispatchEvent('rewardpoints_rewrite_authorizenet_place', array(
            'container' => $container
        ));
        
        if ($container->getRewardPointsDiscount() > 0) {
            $amount -= Mage::app()->getStore()->convertPrice($container->getRewardPointsDiscount());
        }
        
        return parent::_place($payment, $amount, $requestType);
    }
}
