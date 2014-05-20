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
 * RewardPointsReferFriends Model Total Earning
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_Rewardpointsreferfriends_Model_Total_Creditmemo_Earning extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract {

    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
        $order = $creditmemo->getOrder();
        $invited_discount = $order->getRewardpointsInvitedDiscount();
        $base_invited_discount = $order->getRewardpointsInvitedBaseDiscount();

        $creditmemo->setRewardpointsInvitedDiscount($invited_discount);
        $creditmemo->setRewardpointsInvitedBaseDiscount($base_invited_discount);
        
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $invited_discount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $base_invited_discount);

        return $this;
    }
}