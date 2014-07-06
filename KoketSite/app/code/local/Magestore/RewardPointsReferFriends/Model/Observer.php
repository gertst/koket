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
 * RewardPointsReferFriends Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Model_Observer {

    /**
     * process customer register succucess for mageno 1.7xx
     * @param type $observer
     */
//    public function customerRegisterSuccess($observer) {
//
//        $model = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->load($observer->getCustomer()->getId(), 'customer_id');
//        $model->setCustomerId($observer->getCustomer()->getId());
//        try {
//            $model->save();
//        } catch (Exception $exc) {
//            echo $exc->getTraceAsString();
//        }
//    }

    /**
     *  process customer register succucess for mageno below 1.7xx
     * @param type $observer
     * @return type
     */
//    public function customerRegisterSuccessForLow($observer) {
//        if (version_compare(Mage::getVersion(), '1.6.0.0', '>=')) {
//            return;
//        }
//        $model = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->load($observer->getCustomer()->getId(), 'customer_id');
//        $model->setCustomerId($observer->getCustomer()->getId());
//        try {
//            $model->save();
//        } catch (Exception $exc) {
//            echo $exc->getTraceAsString();
//        }
//    }

    public function customerLogin($observer) {
        $model = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->load($observer->getCustomer()->getId(), 'customer_id');
        $model->setCustomerId($observer->getCustomer()->getId());
        try {
            $model->save();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    /**
     * process admin customer save 
     * @param type $observer
     */
    public function customerSaveAfter($observer) {
        $model = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->load($observer->getCustomer()->getId(), 'customer_id');
        $model->setCustomerId($observer->getCustomer()->getId());
        try {
            $model->save();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    /**
     * process controller_action_predispatch event
     *
     * @return Magestore_RewardPointsReferFriends_Model_Observer
     */
    public function actionPredispatch($observer) {
        $key = Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key');

        if ($key) {
            $refer_cus = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->loadByKey($key);
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            if ($customerId == $refer_cus->getCustomerId() && !$refer_cus->validateReferLinkCus())
                Mage::getSingleton('core/cookie')->delete('rewardpoints_offer_key');
        }

        $key = Mage::app()->getRequest()->getParam('k');

        if ($key && Mage::helper('rewardpointsreferfriends')->getReferConfig('refer_method') != 'coupon') {
            $refer_cus = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->loadByKey($key);

            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            if ($refer_cus->getId() && $customerId != $refer_cus->getCustomerId()) {

                if (!Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key') || Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key') != $key) {
                    Mage::getSingleton('core/cookie')->set('rewardpoints_offer_key', $key);
                }
            }
        }

        return $this;
    }

    /**
     * Process order after save
     * 
     * @param type $observer
     * @return Magestore_RewardPoints_Model_Observer
     */
    public function salesOrderSaveAfter($observer) {
        $order = $observer['order'];

        // Add earning point for customer
        if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE && $order->getRewardpointsReferalEarn()
        ) {

            $customer = Mage::getModel('customer/customer')->load($order->getRewardpointsReferCustomerId());
            if (!$customer->getId()) {
                return $this;
            }
            try {
                Mage::helper('rewardpoints/action')->addTransaction(
                        'referfriends', $customer, $order
                );
                return $this;
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        // Refun earning point from customer if order is canceled
        if ($order->getState() == Mage_Sales_Model_Order::STATE_CLOSED && $order->getRewardpointsReferalEarn()) {
            $earnedRefund = (int) Mage::getResourceModel('rewardpoints/transaction_collection')
                            ->addFieldToFilter('action', 'referfriends')
                            ->addFieldToFilter('order_id', $order->getId())
                            ->getFieldTotal();

            if ($earnedRefund <= 0) {
                return $this;
            }
            if ($earnedRefund > $order->getRewardpointsReferalEarn()) {
                $earnedRefund = $order->getRewardpointsReferalEarn();
            }
            if ($earnedRefund > 0) {
                $order->setRefundEarnedPoints($earnedRefund);
                if (empty($customer)) {
                    $customer = Mage::getModel('customer/customer')->load($order->getRewardpointsReferCustomerId());
                }
                if (!$customer->getId()) {
                    return $this;
                }
                Mage::helper('rewardpoints/action')->addTransaction(
                        'referfriends_cancel', $customer, $order
                );
            }
        }

        return $this;
    }

    /**
     * process coupon post apply
     * @param type $observer
     * @return Magestore_RewardPointsReferFriends_Model_Observer
     */
    public function couponPost($observer) {
        if (Mage::helper('rewardpointsreferfriends')->getReferConfig('refer_method') == 'link')
            return $this;
        $action = $observer->getEvent()->getControllerAction();
        $code = trim($action->getRequest()->getParam('coupon_code'));
        if (!$code)
            return $this;

        $refer_cus = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->loadByCoupon($code);

        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if (!$refer_cus->getId() || $refer_cus->getCustomerId() == $customerId) {
            return $this->useDefaultCoupon();
        }
        if (!Mage::getSingleton('checkout/session')->getData('coupon_code'))
            Mage::getSingleton('checkout/session')->setData('coupon_code', $code);
        if ($action->getRequest()->getParam('remove') == 1) {
            if (Mage::getSingleton('checkout/session')->getData('coupon_code'))
                Mage::getSingleton('checkout/session')->setData('coupon_code', '');
            if ($refer_cus->getKey() == Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key')) {

                Mage::getSingleton('core/cookie')->delete('rewardpoints_offer_key');
                Mage::getSingleton('checkout/session')->getMessages(true);
                Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('rewardpointsreferfriends')->__('Coupon code was canceled.'));
            }
        } else {
            Mage::getSingleton('core/cookie')->set('rewardpoints_offer_key', $refer_cus->getKey());


            Mage::getSingleton('checkout/session')->getMessages(true);

            Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('rewardpointsreferfriends')->__('Coupon code "%s" was applied.', $code));
        }
        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        $action->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
    }

    /**
     * use default magento coupon code
     * @return string
     */
    public function useDefaultCoupon() {
        if (Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key'))
            Mage::getSingleton('core/cookie')->delete('rewardpoints_offer_key');
        return;
    }

    /**
     * get data coupon_code
     * @param type $observer
     * @return string
     */
    public function getCouponCode($observer) {
        $coupon = $observer->getContainer();
        if (Mage::getSingleton('checkout/session')->getData('coupon_code')) {
            $coupon->setCouponCode(Mage::getSingleton('checkout/session')->getData('coupon_code'));
        }
        return;
    }

}
