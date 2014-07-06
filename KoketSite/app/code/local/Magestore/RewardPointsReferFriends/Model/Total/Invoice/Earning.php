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
class Magestore_Rewardpointsreferfriends_Model_Total_Invoice_Earning extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
        $order = $invoice->getOrder();
        $invited_discount = $order->getRewardpointsInvitedDiscount();
        $base_invited_discount = $order->getRewardpointsInvitedBaseDiscount();       
        $invoice->setGrandTotal($invoice->getGrandTotal() - $invited_discount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $base_invited_discount);

        return $this;
    }
}