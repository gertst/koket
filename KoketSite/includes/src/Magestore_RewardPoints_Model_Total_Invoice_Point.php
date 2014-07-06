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
 * Rewardpoints Spend for Order by Point Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Total_Invoice_Point extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect total when create Invoice
     * 
     * @param Mage_Sales_Model_Order_Invoice $invoice
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();
		/**
         * update 2.0
         */
        $earnPoint = 0;
        $maxEarn  = $order->getRewardpointsEarn();
        $maxEarn -= (int)Mage::getResourceModel('rewardpoints/transaction_collection')
            ->addFieldToFilter('action', 'earning_invoice')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if ($maxEarn >= 0) {
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $itemPoint  = (int)$orderItem->getRewardpointsEarn();
                $itemPoint  = $itemPoint * $item->getQty() / $orderItem->getQtyOrdered();
                $earnPoint += floor($itemPoint);
            }
            if($invoice->isLast() || $earnPoint >= $maxEarn) $earnPoint = $maxEarn;
            $invoice->setRewardpointsEarn($earnPoint);
        }
        
        if ($order->getRewardpointsDiscount() < 0.0001) {
            return ;
        }
        if ($invoice->isLast()) {
            $baseDiscount   = $order->getRewardpointsBaseDiscount();
            $discount       = $order->getRewardpointsDiscount();
            foreach ($order->getInvoiceCollection() as $existedInvoice) {
                if ($baseDiscount > 0.0001) {
                    $baseDiscount   -= $existedInvoice->getRewardpointsBaseDiscount();
                    $discount       -= $existedInvoice->getRewardpointsDiscount();
                }
            }
        } else {
            $orderTotal = $order->getGrandTotal() + $order->getRewardpointsDiscount();
            $ratio      = $invoice->getGrandTotal() / $orderTotal;
            $baseDiscount   = $order->getRewardpointsBaseDiscount() * $ratio;
            $discount       = $order->getRewardpointsDiscount() * $ratio;
            
            $maxBaseDiscount   = $order->getRewardpointsBaseDiscount();
            $maxDiscount       = $order->getRewardpointsDiscount();
            foreach ($order->getInvoiceCollection() as $existedInvoice) {
                if ($maxBaseDiscount > 0.0001) {
                    $maxBaseDiscount    -= $existedInvoice->getRewardpointsBaseDiscount();
                    $maxDiscount        -= $existedInvoice->getRewardpointsDiscount();
                }
            }
            if ($baseDiscount > $maxBaseDiscount) {
                $baseDiscount   = $maxBaseDiscount;
                $discount       = $maxDiscount;
            }
        }
        
        if ($baseDiscount > 0.0001) {
            if ($invoice->getBaseGrandTotal() <= $baseDiscount) {
                $invoice->setRewardpointsBaseDiscount($invoice->getBaseGrandTotal());
                $invoice->setRewardpointsDiscount($invoice->getGrandTotal());
                $invoice->setBaseGrandTotal(0.0);
                $invoice->setGrandTotal(0.0);
            } else {
                $invoice->setRewardpointsBaseDiscount($baseDiscount);
                $invoice->setRewardpointsDiscount($discount);
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseDiscount);
                $invoice->setGrandTotal($invoice->getGrandTotal() - $discount);
            }
        }
    }
}
